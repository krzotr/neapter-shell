<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class UtilsTest extends PHPUnit_Framework_TestCase
{
    protected $oUtils;

    public function setUp()
    {
        $this->oUtils = new Utils();
    }

    public function testGetTmpDir()
    {
        $this->assertNotSame(null, $this->oUtils->getTmpDir());
    }

    public function testEncryptDecrypt()
    {
        $sData = md5(microtime(1));
        $sKey = uniqid();

        $sEncrypted = $this->oUtils->encrypt($sData, $sKey);
        $sDecrypted = $this->oUtils->decrypt($sEncrypted, $sKey);

        $this->assertSame($sData, $sDecrypted);
    }

    public function testEncryptDecryptEmptyKey()
    {
        $sData = md5(microtime(1));

        $sEncrypted = $this->oUtils->encrypt($sData);
        $sDecrypted = $this->oUtils->decrypt($sEncrypted);

        $this->assertSame($sData, $sDecrypted);
    }

    public function testEncryptEmptyData()
    {
        $this->assertSame('', $this->oUtils->encrypt(''));
    }

    public function testPort()
    {
        $this->assertSame(0, $this->oUtils->getPort('test'));
        $this->assertSame(0, $this->oUtils->getPort('0'));
        $this->assertSame(0, $this->oUtils->getPort('65536'));

        $this->assertSame(65535, $this->oUtils->getPort(65535));
    }

    public function testHostPort()
    {
        $this->assertSame(array(), $this->oUtils->getHostPort('127.0.0.1'));
        $this->assertSame(array(), $this->oUtils->getHostPort('127.0.0.1:XXX'));

        $this->assertSame(
            array('127.0.0.1', 9999),
            $this->oUtils->getHostPort('127.0.0.1:9999')
        );
    }

    public function testIsWindows()
    {
        $this->oUtils->isWindows();
    }

    public function testIsSafeMode()
    {
        $this->assertSame(
            (bool) ini_get('safe_mode'),
            $this->oUtils->isSafeMode()
        );
    }

    public function testIsExecutable()
    {
        $this->oUtils->isExecutable();
    }

    public function testGetDisabledFunctions()
    {
        $this->oUtils->getDisabledFunctions();
    }

    public function testGetSystemFunctions()
    {
        $this->oUtils->getSystemFunctions();
    }

    public function testIsUserAgentOnBlacklist()
    {
        $aBots = array(
            'Mozilla/5.0 (compatible; MJ12bot/v1.4.5; http://www.majestic12.co.uk/bot.php?+)',
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            'Mozilla/5.0 (compatible; AhrefsBot/5.1; +http://ahrefs.com/robot/)',
            'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
            'Mozilla/5.0 (compatible; DotBot/1.1; http://www.opensiteexplorer.org/dotbot, help@moz.com)',
            'Mozilla/5.0 (compatible; SemrushBot/1.1~bl; +http://www.semrush.com/bot.html)',
            'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
            'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
            'Mozilla/5.0 (compatible; SeznamBot/3.2; +http://napoveda.seznam.cz/en/seznambot-intro/)',
            'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.96 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            'Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)'
        );

        foreach ($aBots as $sBot) {
            $this->assertTrue($this->oUtils->isUserAgentOnBlacklist($sBot));
        }

        $aNotBots = array(
            'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.0; rv:46.0) Gecko/20100101 Firefox/46.0',
            'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:28.0) Gecko/20100101 Firefox/28.0',
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:26.1) Gecko/20100101 Firefox/26.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; Trident/7.0; rv:11.0) like Gecko',
            'Mozilla/5.0 (X11; Linux x86_64; rv:43.0) Gecko/20100101 Firefox/43.0 Iceweasel/43.0.4',
            'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:46.0) Gecko/20100101 Firefox/46.0'
        );

        foreach ($aNotBots as $sNotBot) {
            $this->assertFalse($this->oUtils->isUserAgentOnBlacklist($sNotBot));
        }
    }

    public function testGetCommandsByModule()
    {
        $this->assertSame(
            array('credits', 'cr3d1ts'),
            $this->oUtils->getCommandsByModule('ModuleCr3d1ts')
        );
    }

    public function testGetCommands()
    {
        $this->oUtils->getCommands();
    }

    public function testGetModules()
    {
        $this->oUtils->getModules();
    }

    public function testGetEncryptionKey()
    {
        $this->oUtils->getEncryptionKey();
    }

    public function testGetUniquePrefix()
    {
        $this->oUtils->getUniquePrefix();
    }

    public function testCacheDel()
    {
        $this->oUtils->cacheSet('test', 'testx');

        $this->assertSame('testx', $this->oUtils->cacheGet('test'));
        $this->assertTrue(is_file($this->oUtils->cacheGetFile('test')));

        $this->assertTrue($this->oUtils->cacheDel('test'));
        $this->assertFalse(is_file($this->oUtils->cacheGetFile('test')));

        $this->assertTrue($this->oUtils->cacheDel('T3sT'));
    }

    public function testCacheFlush()
    {
        $this->oUtils->cacheSet('test1', 'test');
        $this->oUtils->cacheSet('test2', 'test');
        $this->oUtils->cacheSet('test3', 'test');

        $this->assertTrue(is_file($this->oUtils->cacheGetFile('test1')));
        $this->assertTrue(is_file($this->oUtils->cacheGetFile('test2')));
        $this->assertTrue(is_file($this->oUtils->cacheGetFile('test3')));

        $this->oUtils->cacheFlush();

        $this->assertFalse(is_file($this->oUtils->cacheGetFile('test1')));
        $this->assertFalse(is_file($this->oUtils->cacheGetFile('test2')));
        $this->assertFalse(is_file($this->oUtils->cacheGetFile('test3')));
    }

    public function testCacheGetSet()
    {
        $sContent = md5(microtime(1));

        $this->oUtils->cacheSet('test', $sContent);

        $this->assertSame($sContent, $this->oUtils->cacheGet('test'));
        $this->assertFalse($this->oUtils->cacheGet('invAl!d'));

        $sFile = $this->oUtils->cacheGetFile('test');
        file_put_contents($sFile, 'invalid data in file');

        $this->assertFalse($this->oUtils->cacheGet('test'));
    }

    public function testGetAuthFileKey()
    {
        $this->oUtils->getAuthFileKey();
    }

    public function testCacheGetFile()
    {
        $this->oUtils->cacheGetFile('test');
    }

    public function loadModuleFromLocation()
    {
        $this->assertFalse($this->oUtils->loadModuleFromLocation('/ab/cdf/x'));
    }

    public function dl()
    {

    }

    public function loadModules()
    {
        $this->oUtils->cacheDel('modules');
        $this->assertfalse($this->oUtils->loadModules());
    }

    public function autoloadExtensions()
    {

    }

    public function autoloadExtensionsAdd()
    {

    }

    public function autoloadExtensionsGet()
    {

    }

    public function getEnvironmentPathes()
    {
        $sCache = $_SERVER['PATH'];

        $_SERVER['PATH'] = '';
        $aPathes = $this->oUtils->getPathes();

        $_SERVER['PATH'] = '/abc:/fgh';
        $aPathes = $this->oUtils->getPathes();

        $this->assertTrue(in_array($aPathes), '/abc');
        $this->assertTrue(in_array($aPathes), '/fgh');
        $this->assertTrue(in_array($aPathes), '/usr/bin');
        $this->assertTrue(in_array($aPathes), '/bin');

        $_SERVER['PATH'] = $sCache;
    }


}

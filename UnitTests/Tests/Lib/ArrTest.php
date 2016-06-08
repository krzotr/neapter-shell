<?php

/**
 * Neapter Shell
 *
 * @author    Krzysztof Otręba <krzotr@gmail.com>
 * @copyright Copyright (c) 2012-2016, Krzysztof Otręba
 *
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 */
class ArrTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $aTest = array(
            'one' => array(
                'two' => 2,
                'three' => array(
                    'a' => 'a',
                    'b' => 'b'
                )
            )
        );

        $this->assertSame(2, Arr::get('one.two', $aTest));
        $this->assertSame(false, Arr::get('one.two.three', $aTest));

        $this->assertSame('a', Arr::get('one.three.a', $aTest));
        $this->assertSame('b', Arr::get('one.three.b', $aTest));
    }
}

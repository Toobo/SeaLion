<?php
/*
 * This file is part of the SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toobo\SeaLion\Tests\Input;

use Toobo\SeaLion\Input\InputStorage;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class InputStorageTest extends \PHPUnit_Framework_TestCase
{

    public function testParse()
    {
        $argv = [
            'the:command',
            'argument1',
            '-a_flag',
            '--an_option',
            '-a_empty_flag=',
            '--an_empty_option=',
            'argument2',
            '-a_text_flag=I_am_a_flag',
            '--a_text_option=I_am_an_option',
            '-a_quoted_flag="I am a flag"',
            'argument3',
            "--a_quoted_option='I am an option'",
        ];
        $storage = new InputStorage($argv);
        $expected = [
            'arguments' => [
                0 => 'argument1',
                1 => 'argument2',
                2 => 'argument3',
            ],
            'options'   => [
                'an_option'       => true,
                'an_empty_option' => '',
                'a_text_option'   => 'I_am_an_option',
                'a_quoted_option' => 'I am an option',
            ],
            'flags'     => [
                'a_flag'        => true,
                'a_empty_flag'  => '',
                'a_text_flag'   => 'I_am_a_flag',
                'a_quoted_flag' => 'I am a flag',
            ]
        ];
        assertSame('the:command', $storage['command']);
        assertSame($expected['arguments'], $storage['arguments']);
        assertSame($expected['options'], $storage['options']);
        assertSame($expected['flags'], $storage['flags']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetFailsIfBadKey()
    {
        $storage = new InputStorage(['foo']);
        $storage['foo'];
    }
}

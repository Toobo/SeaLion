<?php
/*
 * This file is part of the Toobo\SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Toobo\SeaLion\Tests\Input;

use Toobo\SeaLion\Input\StringInput;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class StringInputTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $input = new StringInput('bar -foo --yes -bar="bar" arg');
        assertFalse($input->parsed());
        $input->parse();
        assertTrue($input->parsed());
        assertSame('bar', $input->command());
        assertSame(['foo' => true, 'bar' => 'bar'], $input->flags());
        assertSame(['yes' => true], $input->options());
        assertSame(['arg'], $input->arguments());
    }
}

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

use Toobo\SeaLion\Input\ArgvInput;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class ArgvInputTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $input = new ArgvInput(['bar']);
        assertFalse($input->parsed());
        $input->parse();
        assertTrue($input->parsed());
        assertSame('bar', $input->command());
    }
}

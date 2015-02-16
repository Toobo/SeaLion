<?php
/*
 * This file is part of the Toobo\SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Toobo\SeaLion\Tests\Dispatcher;

use Toobo\SeaLion\Dispatcher\Dispatcher;
use Toobo\SeaLion\Router;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{

    public function testErrorConstants()
    {
        $dispatcher = new Dispatcher();
        /** @var \Toobo\SeaLion\Input\InputInterface $input */
        $input = $this->getMockBuilder('Toobo\SeaLion\Input\InputInterface')->getMock();
        $input->method('arguments')->willReturn([]);
        $input->method('options')->willReturn([]);
        $input->method('flags')->willReturn([]);
        $all = [Router::ARGUMENTS, Router::OPTIONS, Router::FLAGS];
        $result1 = $dispatcher->error('foo', $all, $input);
        array_pop($all);
        $result2 = $dispatcher->error('foo', $all, $input);
        $result3 = $dispatcher->error('foo', [Router::COMMAND], $input);
        assertNotEmpty($result1[1] & Router::ARGS_NOT_MATCHED);
        assertNotEmpty($result1[1] & Router::OPTIONS_NOT_MATCHED);
        assertNotEmpty($result1[1] & Router::FLAGS_NOT_MATCHED);
        assertNotEmpty($result1[1] & Router::NOT_MATCHED);
        assertEmpty($result1[1] & Router::COMMAND_NOT_MATCHED);
        assertEmpty($result2[1] & Router::FLAGS_NOT_MATCHED);
        assertNotEmpty($result2[1] & Router::NOT_MATCHED);
        assertNotEmpty($result3[1] & Router::COMMAND_NOT_MATCHED);
        assertEmpty($result3[1] & Router::ARGS_NOT_MATCHED);
        assertNotEmpty($result3[1] & Router::NOT_MATCHED);
    }
}

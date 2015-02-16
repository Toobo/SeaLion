<?php
/*
 * This file is part of the SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toobo\SeaLion\Tests;

use Toobo\SeaLion\Router;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param null|array $parse
     * @return \Toobo\SeaLion\Input\InputInterface
     */
    private function getInput($parse = null)
    {
        $input = $this->getMockBuilder('Toobo\SeaLion\Input\InputInterface')->getMock();
        $input->method('parse')->willReturn($parse);

        return $input;
    }

    /**
     * @return \Toobo\SeaLion\Dispatcher\DispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->getMockBuilder('Toobo\SeaLion\Dispatcher\DispatcherInterface')->getMock();
    }

    /**
     * @return \Toobo\SeaLion\Matcher\MatcherInterface
     */
    private function getMatcher()
    {
        return $this->getMockBuilder('Toobo\SeaLion\Matcher\MatcherInterface')->getMock();
    }

    /**
     * @return array
     */
    private function getFactoryAndRoute()
    {
        /** @var \Toobo\SeaLion\Route\RouteFactoryInterface $factory */
        $factory = $this->getMockBuilder('Toobo\SeaLion\Route\RouteFactoryInterface')->getMock();
        /** @var \Toobo\SeaLion\Route\RouteInterface $route */
        $route = $this->getMockBuilder('Toobo\SeaLion\Route\RouteInterface')->getMock();
        $factory->method('factory')->willReturn($route);

        return [$factory, $route];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddCommandFailsIfNotString()
    {
        $router = new Router();
        $router->addCommand(true, 'foo');
    }

    public function testAddCommandReturnsRoute()
    {
        /** @var \Toobo\SeaLion\Route\RouteFactoryInterface $factory */
        /** @var \Toobo\SeaLion\Route\RouteInterface $route */
        list($factory, $route) = $this->getFactoryAndRoute();
        $router = new Router(null, null, null, $factory);
        assertSame($route, $router->addCommand('foo', 'foo'));
    }

    public function testInvokeErrorIfEmptyCommand()
    {
        $input = $this->getInput();
        $input->method('command')->willReturn(null);
        $dispatcher = $this->getDispatcher();
        $dispatcher->method('error')->with(false, [Router::COMMAND], $input)->willReturn('Error!');
        $router = new Router($input, $dispatcher);
        assertSame('Error!', $router());
    }

    public function testInvokeErrorIfNonValidCommand()
    {
        $input = $this->getInput();
        $input->method('command')->willReturn('Lorem Ipsum');
        $dispatcher = $this->getDispatcher();
        $dispatcher->method('error')->with(false, [Router::COMMAND], $input)->willReturn('Error!');
        $router = new Router($input, $dispatcher);
        assertSame('Error!', $router());
    }

    public function testInvokeSuccess()
    {
        $input = $this->getInput();
        $input->method('command')->willReturn('foo');
        $dispatcher = $this->getDispatcher();
        $dispatcher->method('success')->with('foo', 'handler0', $input)->willReturn('Success!');
        /** @var \Toobo\SeaLion\Route\RouteFactoryInterface $factory */
        /** @var \Toobo\SeaLion\Route\RouteInterface $route */
        list($factory, $route) = $this->getFactoryAndRoute();
        $matcher = $this->getMatcher();
        $matched = [Router::ARGUMENTS, Router::OPTIONS, Router::FLAGS];
        $matcher->method('match')->with($route, $input)->willReturn($matched);
        $router = new Router($input, $dispatcher, $matcher, $factory);
        $router->addCommand('foo', 'handler0');
        assertSame('Success!', $router());
    }

    public function testInvokeErrors()
    {
        $input = $this->getInput();
        $input->method('command')->willReturn('foo');
        $dispatcher = $this->getDispatcher();
        $return = function ($command, $not) {
            if ($command === 'foo') {
                if ($not === [Router::FLAGS]) {
                    return 'Flags!';
                } elseif ($not === [Router::OPTIONS]) {
                    return 'Option!';
                }
            }

            return 'Meh!';
        };
        $dispatcher->method('error')->will($this->returnCallback($return));
        /** @var \Toobo\SeaLion\Route\RouteFactoryInterface $factory */
        /** @var \Toobo\SeaLion\Route\RouteInterface $route */
        list($factory, $route) = $this->getFactoryAndRoute();
        $matcher = $this->getMatcher();
        $matched = [
            [Router::ARGUMENTS, Router::OPTIONS],
            [Router::ARGUMENTS, Router::FLAGS]
        ];
        $matcher->method('match')
                ->with($route, $input)
                ->will($this->onConsecutiveCalls($matched[0], $matched[1]));
        $router = new Router($input, $dispatcher, $matcher, $factory);
        $router->addCommand('foo', 'handler0');
        $router->addCommand('foo', 'handler1');
        assertSame('Option!', $router());
        assertSame(['Flags!', 'Option!'], $router->errors());
    }
}

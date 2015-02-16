<?php
/*
 * This file is part of the SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toobo\SeaLion\Tests\Matcher;

use Toobo\SeaLion\Matcher\Matcher;
use Toobo\SeaLion\Router;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class MatcherTest extends \PHPUnit_Framework_TestCase
{
    private function getRoute($matcher)
    {
        /** @var \Toobo\SeaLion\Route\RouteInterface $route */
        $route = $this->getMockBuilder('Toobo\SeaLion\Route\RouteInterface')->getMock();
        $argumentsCallbacks = [
            function ($value) {
                return $value === 'foo';
            },
            function ($value) {
                return preg_match('/^arg[0-9]+$/', $value);
            },
        ];
        $optionsCallbacks = [
            'opt1' => function ($value, $key) {
                return $key === 'opt1' && $value === 'Ok';
            },
            'opt2' => function ($value, $key) {
                return $key === 'opt2' && (int) $value === 33;
            },
        ];
        $flagsCallbacks = [
            'flag1' => function ($value, $key) {
                return $key === 'flag1' && (bool) $value === true;
            },
            'flag2' => function ($value, $key) {
                return $key === 'flag2' && $value === 'Ok again';
            },
        ];
        $map = [
            ['arguments', $matcher, $argumentsCallbacks],
            ['options', $matcher, $optionsCallbacks],
            ['flags', $matcher, $flagsCallbacks],
        ];
        $route->method('get')->will($this->returnValueMap($map));

        return $route;
    }

    public function testMatchAll()
    {
        $matcher = new Matcher();

        /** @var \Toobo\SeaLion\Input\InputInterface $input */
        $input = $this->getMockBuilder('Toobo\SeaLion\Input\InputInterface')->getMock();
        $input->method('arguments')->willReturn(['foo', 'arg2']);
        $input->method('options')->willReturn(['opt1' => 'Ok', 'opt2' => '33']);
        $input->method('flags')->willReturn(['flag1' => true, 'flag2' => 'Ok again']);

        $expected = [Router::ARGUMENTS, Router::OPTIONS, Router::FLAGS];
        assertSame($expected, $matcher->match($this->getRoute($matcher), $input));
    }

    public function testMatchNone()
    {
        $matcher = new Matcher();

        /** @var \Toobo\SeaLion\Input\InputInterface $input */
        $input = $this->getMockBuilder('Toobo\SeaLion\Input\InputInterface')->getMock();
        $input->method('arguments')->willReturn(['bar', 'arg2']);
        $input->method('options')->willReturn(['opt1' => 'Ok', 'opt2' => 'No!']);
        $input->method('flags')->willReturn(['flag1' => false, 'flag2' => 'Ok again']);

        assertSame([], $matcher->match($this->getRoute($matcher), $input));
    }

    public function testMatchSome()
    {
        $matcher = new Matcher();

        /** @var \Toobo\SeaLion\Input\InputInterface $input */
        $input = $this->getMockBuilder('Toobo\SeaLion\Input\InputInterface')->getMock();
        $input->method('arguments')->willReturn(['foo', 'arg22']);
        $input->method('options')->willReturn(['opt1' => 'Ok', 'opt2' => '22']);
        $input->method('flags')->willReturn(['flag1' => 1, 'flag2' => 'Ok again']);

        $expected = [Router::ARGUMENTS, Router::FLAGS];
        assertSame($expected, $matcher->match($this->getRoute($matcher), $input));
    }

    public function testMatchEmptyRouteMatchAll()
    {
        $matcher = new Matcher();

        /** @var \Toobo\SeaLion\Route\RouteInterface $route */
        $route = $this->getMockBuilder('Toobo\SeaLion\Route\RouteInterface')->getMock();
        $route->method('get')->willReturn([]);

        /** @var \Toobo\SeaLion\Input\InputInterface $input */
        $input = $this->getMockBuilder('Toobo\SeaLion\Input\InputInterface')->getMock();
        $input->method('arguments')->willReturn(['foo', 'arg22']);
        $input->method('options')->willReturn(['opt1' => 'Ok', 'opt2' => '22']);
        $input->method('flags')->willReturn(['flag1' => 1, 'flag2' => 'Ok again']);

        $expected = [Router::ARGUMENTS, Router::OPTIONS, Router::FLAGS];
        assertSame($expected, $matcher->match($route, $input));
    }

    public function testMissingRequirementIsFalse()
    {
        $matcher = new Matcher();

        /** @var \Toobo\SeaLion\Route\RouteInterface $route */
        $route = $this->getMockBuilder('Toobo\SeaLion\Route\RouteInterface')->getMock();
        $argumentsCallbacks = [
            'first' => function ($val) {
                return $val === false;
            },
        ];
        $optionsCallbacks = [
            'test' => function ($val) {
                return ! empty($val);
            },
        ];
        $map = [
            ['arguments', $matcher, $argumentsCallbacks],
            ['options', $matcher, $optionsCallbacks],
            ['flags', $matcher, []],
        ];
        $route->method('get')->will($this->returnValueMap($map));

        /** @var \Toobo\SeaLion\Input\InputInterface $input */
        $input = $this->getMockBuilder('Toobo\SeaLion\Input\InputInterface')->getMock();
        $input->method('arguments')->willReturn([]);
        $input->method('options')->willReturn([]);
        $input->method('flags')->willReturn([]);

        $expected = [Router::ARGUMENTS, Router::FLAGS];
        assertSame($expected, $matcher->match($route, $input));
    }
}

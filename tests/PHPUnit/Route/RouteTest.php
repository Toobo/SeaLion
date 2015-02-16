<?php
/*
 * This file is part of the SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toobo\SeaLion\Tests\Route;

use Toobo\SeaLion\Route\Route;
use Toobo\SeaLion\Router;
use Toobo\SeaLion\Matcher\Matcher;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Toobo\SeaLion\Matcher\MatcherInterface
     */
    private $matcher;

    private function getRoute()
    {
        $this->matcher = $this->getMockBuilder('Toobo\SeaLion\Matcher\MatcherInterface')->getMock();

        return new Route($this->matcher);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetFailsIfWrongMatcher()
    {
        $route = $this->getRoute();
        $route->get(Router::ARGUMENTS, new Matcher());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetFailsIfWrongWhich()
    {
        $route = $this->getRoute();
        $route->get('foo', $this->matcher);
    }

    public function testBuildFromString()
    {
        $route = $this->getRoute()
                      ->withArguments(['Arg!'])
                      ->withOptions(['opt' => 'Opt!'])
                      ->withFlags(['flag' => 'Flag!']);
        /** @var \Closure[] */
        $arg = $route->get(Router::ARGUMENTS, $this->matcher);
        /** @var \Closure[] */
        $opt = $route->get(Router::OPTIONS, $this->matcher);
        /** @var \Closure[] */
        $flag = $route->get(Router::FLAGS, $this->matcher);
        assertTrue($arg[0]('Arg!', 0));
        assertFalse($arg[0]('Arg', 0));
        assertTrue($opt['opt']('Opt!', 'opt'));
        assertFalse($opt['opt']('Opt', 'opt'));
        assertTrue($flag['flag']('Flag!', 'flag'));
        assertFalse($flag['flag']('Flag', 'flag'));
    }

    public function testBuildFromBool()
    {
        $route = $this->getRoute()
                      ->withArguments([true])
                      ->withOptions(['opt' => true]);
        /** @var \Closure[] */
        $arg = $route->get(Router::ARGUMENTS, $this->matcher);
        /** @var \Closure[] */
        $opt = $route->get(Router::OPTIONS, $this->matcher);
        assertTrue($arg[0]('foo', 0));
        assertFalse($arg[0]('', 0));
        assertTrue($opt['opt']('1', 'opt'));
        assertFalse($opt['opt']('', 'opt'));
    }

    public function testBuildFromRegex()
    {
        $route = $this->getRoute()
                      ->withArguments(['R{/^arg[0-9]{1}$/}'])
                      ->withOptions(['opt' => 'R{/^opt[0-9]{1}$/}']);
        /** @var \Closure[] */
        $arg = $route->get(Router::ARGUMENTS, $this->matcher);
        /** @var \Closure[] */
        $opt = $route->get(Router::OPTIONS, $this->matcher);
        assertTrue($arg[0]('arg1', 0));
        assertFalse($arg[0]('foo', 0));
        assertTrue($opt['opt']('opt1', 'opt'));
        assertFalse($opt['opt']('meh', 'opt'));
    }

    public function testBuildFromCallable()
    {
        $cb = function ($value, $key) {
            return is_numeric($key) ? $value === 'Argument!' : $value === 'Option!';
        };
        $route = $this->getRoute()
                      ->withArguments([$cb])
                      ->withOptions(['opt' => $cb]);
        /** @var \Closure[] */
        $arg = $route->get(Router::ARGUMENTS, $this->matcher);
        /** @var \Closure[] */
        $opt = $route->get(Router::OPTIONS, $this->matcher);
        assertTrue($arg[0]('Argument!', 0));
        assertFalse($arg[0]('Argument', 0));
        assertTrue($opt['opt']('Option!', 'opt'));
        assertFalse($opt['opt']('Option', 'opt'));
    }
}

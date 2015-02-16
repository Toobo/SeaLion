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

use Toobo\SeaLion\Route\RouteFactory;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class RouteFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testFactoryImplementsContract()
    {
        $factory = new RouteFactory();
        /** @var \Toobo\SeaLion\Matcher\MatcherInterface $matcher */
        $matcher = $this->getMockBuilder('Toobo\SeaLion\Matcher\MatcherInterface')->getMock();
        assertInstanceOf(RouteFactory::CONTRACT, $factory->factory($matcher));
    }

    public function testFactoryWrongClassNotUsed()
    {
        $factory = new RouteFactory('stdClass');
        /** @var \Toobo\SeaLion\Matcher\MatcherInterface $matcher */
        $matcher = $this->getMockBuilder('Toobo\SeaLion\Matcher\MatcherInterface')->getMock();
        assertInstanceOf('Toobo\SeaLion\Route\Route', $factory->factory($matcher));
    }

    public function testFactoryGoodClassUsed()
    {
        /** @var \Toobo\SeaLion\Route\RouteInterface $mock */
        $mock = $this->getMockBuilder('Toobo\SeaLion\Route\RouteInterface')->getMock();
        $factory = new RouteFactory(get_class($mock));
        /** @var \Toobo\SeaLion\Matcher\MatcherInterface $matcher */
        $matcher = $this->getMockBuilder('Toobo\SeaLion\Matcher\MatcherInterface')->getMock();
        assertInstanceOf(RouteFactory::CONTRACT, $factory->factory($matcher));
        assertNotInstanceOf('Toobo\SeaLion\Route\Route', $factory->factory($matcher));
    }
}

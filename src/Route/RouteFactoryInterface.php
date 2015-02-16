<?php
/*
 * This file is part of the Toobo\SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Toobo\SeaLion\Route;

use Toobo\SeaLion\Matcher\MatcherInterface;

/**
 * Create instances of routes.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
interface RouteFactoryInterface
{
    const CONTRACT = 'Toobo\SeaLion\Route\RouteInterface';

    /**
     * @param  \Toobo\SeaLion\Matcher\MatcherInterface $matcher The matcher that will be used to
     *                                                          match the route.
     * @return \Toobo\SeaLion\Route\RouteInterface
     */
    public function factory(MatcherInterface $matcher);
}

<?php
/*
 * This file is part of the Toobo\SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Toobo\SeaLion\Matcher;

use Toobo\SeaLion\Route\RouteInterface;
use Toobo\SeaLion\Input\InputInterface;

/**
 * Compare route constraints and input, in order to determine whether the route matched or not.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion\Dispatcher
 */
interface MatcherInterface
{
    /**
     * @param  \Toobo\SeaLion\Route\RouteInterface $route
     * @param  \Toobo\SeaLion\Input\InputInterface $inputParser
     * @return array
     */
    public function match(RouteInterface $route, InputInterface $inputParser);
}

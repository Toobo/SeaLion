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
 * Holds route constraints for arguments, options and flags.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
interface RouteInterface
{
    /**
     * Adds arguments constraints to Route
     *
     * @param  array                               $arguments
     * @return \Toobo\SeaLion\Route\RouteInterface Itself
     */
    public function withArguments(array $arguments);

    /**
     * Adds options constraints to Route
     *
     * @param  array                               $options
     * @return \Toobo\SeaLion\Route\RouteInterface Itself
     */
    public function withOptions(array $options);

    /**
     * Adds flags constraints to Route
     *
     * @param  array                               $flags
     * @return \Toobo\SeaLion\Route\RouteInterface Itself
     */
    public function withFlags(array $flags);

    /**
     * Returns specific constraints to be used by passed matcher, that should be somehow allowed
     * to access route internal state.
     *
     * @param  string                                  $which
     * @param  \Toobo\SeaLion\Matcher\MatcherInterface $matcher
     * @return array
     */
    public function get($which, MatcherInterface $matcher);
}

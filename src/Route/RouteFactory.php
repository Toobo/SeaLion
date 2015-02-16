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
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
class RouteFactory implements RouteFactoryInterface
{
    /**
     * @var string
     */
    private $class = '\Toobo\SeaLion\Route\Route';

    /**
     * @param null|string $class Optional custom route class
     */
    public function __construct($class = null)
    {
        if (is_string($class) && class_exists($class) && is_subclass_of($class, self::CONTRACT)) {
            $this->class = $class;
        }
    }

    /**
     * @inheritdoc
     */
    public function factory(MatcherInterface $matcher)
    {
        $class = $this->class;

        return new $class($matcher);
    }
}

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

use Toobo\SeaLion\Router;
use Toobo\SeaLion\Matcher\MatcherInterface;
use LogicException;
use InvalidArgumentException;
use RuntimeException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
class Route implements RouteInterface
{
    /**
     * @var string
     */
    private $matcherID;

    /**
     * @var array
     */
    private $storage = [Router::ARGUMENTS => [], Router::OPTIONS => [], Router::FLAGS => []];

    /**
     * @var array
     */
    private $locked = [];

    /**
     * @param \Toobo\SeaLion\Matcher\MatcherInterface $matcher
     */
    public function __construct(MatcherInterface $matcher)
    {
        $this->matcherID = spl_object_hash($matcher);
    }

    /**
     * @inheritdoc
     */
    public function withArguments(array $arguments = [])
    {
        $this->addConstraints($arguments, Router::ARGUMENTS);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withOptions(array $options = [])
    {
        $this->addConstraints($options, Router::OPTIONS);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withFlags(array $flags = [])
    {
        $this->addConstraints($flags, Router::FLAGS);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function get($which, MatcherInterface $matcher)
    {
        if (spl_object_hash($matcher) !== $this->matcherID) {
            throw new RuntimeException('Only related matcher can query a Route for its state.');
        }
        if (! array_key_exists($which, $this->storage)) {
            throw new InvalidArgumentException('Only arguments, options and flags can be get from a Route.');
        }

        return $this->storage[$which];
    }

    /**
     * @param array  $constraints
     * @param string $which
     */
    private function addConstraints(array $constraints = [], $which)
    {
        if (in_array($which, $this->locked, true)) {
            throw new LogicException(ucfirst($which).' can be set only once for a Route . ');
        }
        $this->locked[] = $which;
        $this->storage[$which] = ($which === Router::ARGUMENTS)
            ? $this->parseArgs($constraints)
            : $this->parseOptions($constraints);
    }

    /**
     * @param  array $args
     * @return array
     */
    private function parseArgs(array $args)
    {
        ksort($args, SORT_NUMERIC);

        return array_filter(array_map([$this, 'buildCallback'], array_values($args)));
    }

    /**
     * @param  array $opts
     * @return array
     */
    private function parseOptions(array $opts)
    {
        array_walk($opts, function (&$opt, $key) {
            $opt = $this->parseOption($opt, $key);
        });

        return array_filter($opts);
    }

    /**
     * @param  mixed         $opt
     * @param  string        $key
     * @return callable|null
     */
    private function parseOption($opt, $key)
    {
        return is_string($key) && ! is_numeric($key) ? $this->buildCallback($opt) : null;
    }

    /**
     * @param  mixed    $arg
     * @return callable
     */
    private function buildCallback($arg)
    {
        if (is_callable($arg)) {
            return $arg;
        }

        return (is_bool($arg) || in_array($arg, [0, 1], true))
            ? $this->buildFromBool($arg)
            : $this->buildFromString($arg);
    }

    /**
     * @param  bool     $arg
     * @return callable
     */
    private function buildFromBool($arg)
    {
        return function ($value) use ($arg) {
            $val = true;
            if (in_array(strtolower(strval($value)), ['0', 'false', ''])) {
                $val = false;
            }

            return $val === $arg;
        };
    }

    /**
     * @param  mixed    $arg
     * @return callable
     */
    private function buildFromString($arg)
    {
        $val = strval($arg);

        return preg_match('/^(R\{){1}(.+)\}{1}$/', $val, $matches) === 1
            ? $this->buildFromRegex($matches[2])
            : $this->buildIdentity($val);
    }

    /**
     * @param  string   $regex
     * @return callable
     */
    private function buildFromRegex($regex)
    {
        return function ($value) use ($regex) {
            return preg_match($regex, $value) === 1;
        };
    }

    /**
     * @param  string   $string
     * @return callable
     */
    private function buildIdentity($string)
    {
        return function ($value) use ($string) {
            return strtolower($string) === strtolower($value);
        };
    }
}

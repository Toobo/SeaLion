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

use Toobo\SeaLion\Router;
use Toobo\SeaLion\Route\RouteInterface;
use Toobo\SeaLion\Input\InputInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion\Dispatcher
 */
class Matcher implements MatcherInterface
{
    private static $all = [Router::ARGUMENTS, Router::OPTIONS, Router::FLAGS];

    /**
     * @inheritdoc
     */
    public function match(RouteInterface $route, InputInterface $inputParser)
    {
        return array_values(array_filter(self::$all, function ($which) use ($route, $inputParser) {
            $data = $this->getData($inputParser, $which) ?: [];
            $constraints = $route->get($which, $this);
            if ($which === Router::ARGUMENTS) {
                $data = array_values($data);
                $constraints = array_values($constraints);
            }

            return $this->matchConstraints($constraints, $data);
        }));
    }

    /**
     * @param  array $constraints
     * @param  array $data
     * @return bool
     */
    private function matchConstraints(array $constraints, array $data)
    {
        $callbacks = array_filter($constraints, 'is_callable');
        if (empty($callbacks)) {
            return true;
        }
        foreach ($callbacks as $key => $callback) {
            $value = isset($data[$key]) ? $data[$key] : false;
            if (! $callback($value, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  \Toobo\SeaLion\Input\InputInterface $inputParser
     * @param  string                              $which
     * @return array
     */
    private function getData(InputInterface $inputParser, $which)
    {
        switch ($which) {
            case Router::ARGUMENTS:
                return $inputParser->arguments();
            case Router::OPTIONS:
                return $inputParser->options();
            case Router::FLAGS:
                return $inputParser->flags();
        }

        return [];
    }
}

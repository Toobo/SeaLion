<?php
/*
 * This file is part of the Toobo\SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Toobo\SeaLion;

use Toobo\SeaLion\Input\InputInterface;
use Toobo\SeaLion\Input\ArgvInput;
use Toobo\SeaLion\Matcher\MatcherInterface;
use Toobo\SeaLion\Matcher\Matcher;
use Toobo\SeaLion\Dispatcher\DispatcherInterface;
use Toobo\SeaLion\Dispatcher\Dispatcher;
use Toobo\SeaLion\Route\RouteFactoryInterface;
use Toobo\SeaLion\Route\RouteFactory;
use SplQueue;
use InvalidArgumentException;

/**
 * @author   Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @package  Toobo\SeaLion
 */
class Router
{
    const COMMAND             = 'command';
    const ARGUMENTS           = 'arguments';
    const OPTIONS             = 'options';
    const FLAGS               = 'flags';
    const NOT_MATCHED         = 1;
    const COMMAND_NOT_MATCHED = 16;
    const ARGS_NOT_MATCHED    = 8;
    const OPTIONS_NOT_MATCHED = 4;
    const FLAGS_NOT_MATCHED   = 2;

    /**
     * @var \SplQueue[]
     */
    private $routes;

    /**
     * @var \Toobo\SeaLion\Input\InputInterface
     */
    private $input;

    /**
     * @var \Toobo\SeaLion\Matcher\MatcherInterface
     */
    private $matcher;

    /**
     * @var \Toobo\SeaLion\Dispatcher\DispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \Toobo\SeaLion\Route\RouteFactoryInterface
     */
    private $routeFactory;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var mixed
     */
    private $response;

    /**
     * @param \Toobo\SeaLion\Input\InputInterface           $input
     * @param \Toobo\SeaLion\Dispatcher\DispatcherInterface $dispatcher
     * @param \Toobo\SeaLion\Matcher\MatcherInterface       $matcher
     * @param \Toobo\SeaLion\Route\RouteFactoryInterface    $routeFactory
     */
    public function __construct(
        InputInterface $input = null,
        DispatcherInterface $dispatcher = null,
        MatcherInterface $matcher = null,
        RouteFactoryInterface $routeFactory = null
    ) {
        $this->routes = [];
        $this->input = $input ?: new ArgvInput();
        $this->dispatcher = $dispatcher ?: new Dispatcher();
        $this->matcher = $matcher ?: new Matcher();
        $this->routeFactory = $routeFactory ?: new RouteFactory();
    }

    /**
     * Adds a command route and assign to it an handler.
     * Return just created route to allow adding constraints to it.
     *
     * @param  string                              $command
     * @param  mixed                               $handler
     * @return \Toobo\SeaLion\Route\RouteInterface
     */
    public function addCommand($command, $handler)
    {
        if (! is_string($command) || empty($command)) {
            throw new InvalidArgumentException('Route command name must be in a string.');
        }
        /** @var \Toobo\SeaLion\Route\RouteInterface $route */
        $route = $this->routeFactory->factory($this->matcher);
        if (! isset($this->routes[$command])) {
            $this->routes[$command] = new SplQueue();
        }
        $this->routes[$command]->enqueue([$route, $handler]);

        return $route;
    }

    /**
     * Uses input to parse added routes, then matches them using matcher and finally returns
     * result using dispatcher.
     *
     * @return array|mixed Default dispatcher returns an array, custom ones can return anything
     */
    public function __invoke()
    {
        if (! is_null($this->response)) {
            return $this->response;
        }
        $this->input->parse();
        $command = $this->input->command();
        if (empty($command) || ! isset($this->routes[$command])) {
            return $this->dispatcher->error(false, [self::COMMAND], $this->input);
        }
        /** @var \SplQueue $queue */
        $queue = $this->routes[$command];
        $error = true;
        while (! $queue->isEmpty() && ! empty($error)) {
            /** @var \Toobo\SeaLion\Route\RouteInterface $route */
            /** @var mixed $handler */
            list($route, $handler) = $queue->dequeue();
            $all = [self::ARGUMENTS, self::OPTIONS, self::FLAGS];
            /** @var array $matched */
            $matched = $this->matcher->match($route, $this->input);
            /** @var array $notMatched */
            $notMatched = array_values(array_diff($all, $matched));
            $error = empty($notMatched)
                ? null
                : $this->dispatcher->error($command, $notMatched, $this->input);
            $this->errors[] = $error;
        }
        if (is_null($error)) {
            $this->errors = [];
            $this->response = $this->dispatcher->success($command, $handler, $this->input);
        } else {
            $this->response = $error;
        }

        return $this->response;
    }

    /**
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
}

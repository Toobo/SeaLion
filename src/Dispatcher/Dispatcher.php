<?php
/*
 * This file is part of the Toobo\SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Toobo\SeaLion\Dispatcher;

use Toobo\SeaLion\Router;
use Toobo\SeaLion\Input\InputInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * @param  string                              $command
     * @param  mixed                               $handler
     * @param  \Toobo\SeaLion\Input\InputInterface $inputParser
     * @return array
     */
    public function success($command, $handler, InputInterface $inputParser)
    {
        return [
            true,
            $handler,
            $command,
            $this->getInput($inputParser)
        ];
    }

    /**
     * @param  bool|string                         $command
     * @param  array                               $notMatched
     * @param  \Toobo\SeaLion\Input\InputInterface $inputParser
     * @return array
     */
    public function error($command, array $notMatched, InputInterface $inputParser)
    {
        return [
            false,
            $this->errorConstants($notMatched),
            $command,
            $this->getInput($inputParser)
        ];
    }

    /**
     * @param  array $notMatched
     * @return mixed
     */
    private function errorConstants(array $notMatched)
    {
        $map = [
            Router::COMMAND   => Router::COMMAND_NOT_MATCHED,
            Router::ARGUMENTS => Router::ARGS_NOT_MATCHED,
            Router::OPTIONS   => Router::OPTIONS_NOT_MATCHED,
            Router::FLAGS     => Router::FLAGS_NOT_MATCHED,
        ];

        return array_reduce($notMatched, function ($carry, $which) use ($map) {
            return $carry |= $map[$which];
        }, Router::NOT_MATCHED);
    }

    /**
     * @param  \Toobo\SeaLion\Input\InputInterface $inputParser
     * @return array
     */
    private function getInput(InputInterface $inputParser)
    {
        return [
            Router::ARGUMENTS => $inputParser->arguments(),
            Router::OPTIONS   => $inputParser->options(),
            Router::FLAGS     => $inputParser->flags()
        ];
    }
}

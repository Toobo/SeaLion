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

use Toobo\SeaLion\Input\InputInterface;

/**
 * After the matching happened returns information on matched or not-matched route.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
interface DispatcherInterface
{

    /**
     * @param  string                              $command
     * @param  mixed                               $handler
     * @param  \Toobo\SeaLion\Input\InputInterface $inputParser
     * @return mixed
     */
    public function success($command, $handler, InputInterface $inputParser);

    /**
     * @param  string|bool                         $command
     * @param  array                               $notMatched
     * @param  \Toobo\SeaLion\Input\InputInterface $inputParser
     * @return mixed
     */
    public function error($command, array $notMatched, InputInterface $inputParser);
}

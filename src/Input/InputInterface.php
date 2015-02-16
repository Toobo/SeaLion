<?php
/*
 * This file is part of the Toobo\SeaLion package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Toobo\SeaLion\Input;

/**
 * Parses input and gives access to used command, arguments, options and flags.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
interface InputInterface
{
    /**
     * Parses input to find arguments, options and flags
     *
     * @return void
     */
    public function parse();

    /**
     * Do parsing happen?
     *
     * @return bool
     */
    public function parsed();

    /**
     * Returns the command after parsing happened.
     *
     * @return string
     */
    public function command();

    /**
     * Returns arguments after parsing happened.
     *
     * @return string
     */
    public function arguments();

    /**
     * Returns options after parsing happened.
     *
     * @return string
     */
    public function options();

    /**
     * Returns flags after parsing happened.
     *
     * @return string
     */
    public function flags();
}

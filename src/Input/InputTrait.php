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

use InvalidArgumentException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
trait InputTrait
{
    /**
     * @var \Toobo\SeaLion\Input\InputStorage
     */
    private $storage;

    /**
     * @var bool
     */
    private $parsed = false;

    private function useStorage(InputStorage $storage)
    {
        $this->storage = $storage;
        $this->parsed = true;
    }

    private function assertParsed()
    {
        if (! $this->parsed()) {
            throw new InvalidArgumentException("It isn't possible to get information on non-parsed input.");
        }
    }

    public function parsed()
    {
        return $this->parsed;
    }

    /**
     * Returns the command after parsing happened.
     *
     * @return string
     */
    public function command()
    {
        $this->assertParsed();

        return $this->storage['command'];
    }

    /**
     * Returns arguments after parsing happened.
     *
     * @return string
     */
    public function arguments()
    {
        $this->assertParsed();

        return $this->storage['arguments'];
    }

    /**
     * Returns options after parsing happened.
     *
     * @return string
     */
    public function options()
    {
        $this->assertParsed();

        return $this->storage['options'];
    }

    /**
     * Returns flags after parsing happened.
     *
     * @return string
     */
    public function flags()
    {
        $this->assertParsed();

        return $this->storage['flags'];
    }
}

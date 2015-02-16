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
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
class ArgvInput implements InputInterface
{
    use InputTrait;

    /**
     * @var array
     */
    private $tokens;

    /**
     * @inheritdoc
     */
    public function __construct(array $argv = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
            empty($argv) or array_shift($argv); // strip the application name
        }
        $this->tokens = $argv;
    }

    /**
     * @inheritdoc
     */
    public function parse()
    {
        if (! $this->parsed() && ! empty($this->tokens)) {
            $this->useStorage(new InputStorage($this->tokens));
        }
    }
}

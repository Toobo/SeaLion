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

use ArrayAccess;
use InvalidArgumentException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 */
final class InputStorage implements ArrayAccess
{
    const REGEX = '/^(-{1,2})([^=\s\'\"]+)(={1}[\'\"]{1}(.*)[\'\"]{1}|={1}([^\s]*))?$/';

    /**
     * @var array
     */
    private $storage = [
        'arguments' => [],
        'options'   => [],
        'flags'     => [],
    ];

    /**
     * @param array $tokens
     */
    public function __construct(array $tokens)
    {
        $this->storage['command'] = ! empty($tokens) ? array_shift($tokens) : false;
        empty($tokens) or array_walk($tokens, [$this, 'parseToken']);
    }

    /**
     * @param string $token
     */
    private function parseToken($token)
    {
        if (preg_match(self::REGEX, trim($token), $matches) !== 1) {
            $this->storage['arguments'][] = $token;
        } else {
            $which = $matches[1] === '-' ? 'flags' : 'options';
            $this->storage[$which][$matches[2]] = count($matches) === 3
                ? true
                : (isset($matches[5]) ? $matches[5] : $matches[4]);
        }
    }

    /**
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->storage[$offset]);
    }

    /**
     * @param  mixed                     $offset
     * @return array|string
     * @throws \InvalidArgumentException
     */
    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            throw new InvalidArgumentException($offset.'is not a valid input value.');
        }

        return $this->storage[$offset];
    }

    /**
     * @param  string                    $offset
     * @param  mixed                     $value
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        throw new InvalidArgumentException('Input storage is a read-only object.');
    }

    /**
     * @param  string                    $offset
     * @throws \InvalidArgumentException
     */
    public function offsetUnset($offset)
    {
        throw new InvalidArgumentException('Input storage is a read-only object.');
    }
}

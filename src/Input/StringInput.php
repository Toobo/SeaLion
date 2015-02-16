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
 * Parses string to "simulate" console input.
 *
 * This class contains code from the Symfony\Component\Console\Input\StringInput class that is
 * part of Symfony package console component, owned by Fabien Potencier <fabien@symfony.com> and
 * released under MIT license.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Toobo\SeaLion
 * @link    http://symfony.com/components/Console
 * @link    https://github.com/symfony/Console/blob/master/LICENSE
 * @see     https://github.com/symfony/Console/blob/master/Input/StringInput.php
 */
class StringInput implements InputInterface
{
    use InputTrait;

    const REGEX_STRING        = '([^\s]+?)(?:\s|(?<!\\\\)"|(?<!\\\\)\'|$)';
    const REGEX_QUOTED_STRING = '(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')';

    /**
     * @var string
     */
    private $input;

    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Convert a string to an $argv-like array, then set input information accordingly.
     *
     * @inheritdoc
     */
    public function parse()
    {
        if ($this->parsed()) {
            return;
        }
        $tokens = [];
        $length = strlen($this->input);
        $cursor = 0;
        while ($cursor < $length) {
            if ($match = $this->match($cursor, 0)) {
            } elseif ($match = $this->match($cursor, 1)) {
                $search = ['"\'', '\'"', '\'\'', '""'];
                $part = str_replace($search, '', substr($match[3], 1, strlen($match[3]) - 2));
                $tokens[] = $match[1].$match[2].stripcslashes($part);
            } elseif ($match = $this->match($cursor, 2)) {
                $tokens[] = stripcslashes(substr($match[0], 1, strlen($match[0]) - 2));
            } elseif ($match = $this->match($cursor, 3)) {
                $tokens[] = stripcslashes($match[1]);
            } else {
                $m = 'Unable to parse input near "... %s ..."';
                throw new InvalidArgumentException(sprintf($m, substr($this->input, $cursor, 10)));
            }
            $cursor += strlen($match[0]);
        }
        $this->useStorage(new InputStorage($tokens));
    }

    private function match($cursor, $type = 0)
    {
        $regex = [
            '\s+',
            '([^="\'\s]+?)(=?)('.self::REGEX_QUOTED_STRING.'+)',
            self::REGEX_QUOTED_STRING,
            self::REGEX_STRING,
        ];

        return preg_match('/'.$regex[$type].'/A', $this->input, $match, null, $cursor) === 1
            ? $match
            : false;
    }
}

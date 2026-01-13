<?php
declare(strict_types=1);

namespace Thorm\UI\Components;

use InvalidArgumentException;
use Thorm\IR\Node\Node;

use function Thorm\cls;
use function Thorm\el;

final class Section
{
    public static function get(array $children, array $opts = []): Node
    {
        if(empty($children)) {
            throw new InvalidArgumentException('Section: $slots cannot be empty.');
        }

        $node = el('section', [ 
            cls($opts['style']) 
        ],
            $children
        );

        return $node;
    }
}

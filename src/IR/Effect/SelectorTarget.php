<?php
declare(strict_types=1);
namespace PhpJs\IR\Effect;

use InvalidArgumentException;

final class SelectorTarget implements EffectTarget {
    public function __construct(public readonly string $selector) {
        if (trim($selector) === '') throw new InvalidArgumentException('SelectorTarget: selector cannot be empty');
    }
    public function jsonSerialize(): array { return ['type'=>'selector','selector'=>$this->selector]; }
}

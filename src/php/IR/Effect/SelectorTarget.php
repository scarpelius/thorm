<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;

/**
 * Effect target bound to a CSS selector.
 *
 * @group IR/Effect
 * @example
 * $target = new SelectorTarget('#app');
 */
final class SelectorTarget implements EffectTarget {
    /**
     * Build a selector target.
     *
     * @param string $selector CSS selector string.
     */
    public function __construct(public readonly string $selector) {
        if (trim($selector) === '') throw new InvalidArgumentException('SelectorTarget: selector cannot be empty');
    }

    /**
     * Encode this target as runtime IR payload.
     *
     * @return array<string, string>
     */
    public function jsonSerialize(): array { return ['type'=>'selector','selector'=>$this->selector]; }
}

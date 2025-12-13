<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;

final class VisibleTrigger implements EffectTrigger {
    public function __construct(
        public readonly float $threshold = 0.0,
        public readonly ?string $rootMargin = null,
        public readonly ?string $when = 'enter'
    ) {
        if ($threshold < 0.0 || $threshold > 1.0) throw new InvalidArgumentException('VisibleTrigger: threshold 0..1');
        if ($rootMargin !== null && trim($rootMargin) === '') throw new InvalidArgumentException('VisibleTrigger: rootMargin non-empty if provided');
    }

    public function type(): string { return 'visible'; }

    public function jsonSerialize(): array {
        $out = ['type'=>'visible','threshold'=>$this->threshold, 'when' => $this->when];
        if ($this->rootMargin !== null) $out['rootMargin'] = $this->rootMargin;
        return $out;
    }
}

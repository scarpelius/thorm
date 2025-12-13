<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;

final class IntervalTrigger implements EffectTrigger {
    public function __construct(public readonly int $ms) {
        if ($ms <= 0) throw new InvalidArgumentException('IntervalTrigger: ms must be > 0.');
    }
    public function type(): string { return 'interval'; }
    public function jsonSerialize(): array { return ['type'=>'interval','ms'=>$this->ms]; }
}

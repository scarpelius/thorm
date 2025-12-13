<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;

final class TimeoutTrigger implements EffectTrigger {
    public function __construct(public readonly int $ms) {
        if ($ms <= 0) throw new InvalidArgumentException('TimeoutTrigger: ms must be > 0.');
    }
    public function type(): string { return 'timeout'; }
    public function jsonSerialize(): array { return ['type'=>'timeout','ms'=>$this->ms]; }
}

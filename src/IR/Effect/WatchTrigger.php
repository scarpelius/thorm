<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;
use Thorm\IR\Expr\Expr;

final class WatchTrigger implements EffectTrigger {
    public function __construct(
        public readonly Expr $expr,
        public readonly bool $immediate = false,
        public readonly int|Expr|null $debounceMs = null,
        public readonly int|Expr|null $throttleMs = null,
    ) {
        $this->validate();
    }
    public function type(): string { return 'watch'; }
    public function jsonSerialize(): array {
        $out = ['type'=>'watch','expr'=>$this->expr->jsonSerialize(),'immediate'=>$this->immediate];
        if ($this->debounceMs !== null) $out['debounceMs'] = $this->debounceMs;
        if ($this->throttleMs !== null) $out['throttleMs'] = $this->throttleMs;
        return $out;
    }

    private function validate() {
        if ( !$this->debounceMs instanceof Expr
            && $this->debounceMs !== null 
            && $this->debounceMs <= 0
        ) throw new InvalidArgumentException('WatchTrigger: debounceMs > 0 or null.');
        if ( !$this->throttleMs instanceof Expr
            && $this->throttleMs !== null 
            && $this->throttleMs <= 0
        ) throw new InvalidArgumentException('WatchTrigger: throttleMs > 0 or null.');
    }
}

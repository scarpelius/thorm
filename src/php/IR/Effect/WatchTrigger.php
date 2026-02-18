<?php
declare(strict_types=1);
namespace Thorm\IR\Effect;

use InvalidArgumentException;
use Thorm\IR\Expr\Expr;

/**
 * Effect trigger for expression dependency changes.
 *
 * @group IR/Effect
 * @example
 * $trigger = new WatchTrigger(Expr::read($count), true, 100, null);
 */
final class WatchTrigger implements EffectTrigger {
    /**
     * Build a watch trigger.
     *
     * @param Expr $expr Expression to observe.
     * @param bool $immediate Run once immediately when mounted.
     * @param int|Expr|null $debounceMs Optional debounce delay.
     * @param int|Expr|null $throttleMs Optional throttle delay.
     */
    public function __construct(
        public readonly Expr $expr,
        public readonly bool $immediate = false,
        public readonly int|Expr|null $debounceMs = null,
        public readonly int|Expr|null $throttleMs = null,
    ) {
        $this->validate();
    }

    /**
     * Return trigger discriminator.
     *
     * @return string
     */
    public function type(): string { return 'watch'; }

    /**
     * Encode this trigger as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        $out = ['type'=>'watch','expr'=>$this->expr->jsonSerialize(),'immediate'=>$this->immediate];
        if ($this->debounceMs !== null) $out['debounceMs'] = $this->debounceMs;
        if ($this->throttleMs !== null) $out['throttleMs'] = $this->throttleMs;
        return $out;
    }

    /**
     * Validate timing options.
     *
     * @return void
     */
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

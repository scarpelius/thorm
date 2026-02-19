<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use InvalidArgumentException;

/**
 * Action that delays one or more actions by a fixed interval.
 *
 * @group IR/Action
 * @example
 * $action = new DelayAction(300, [new IncAction(1, 1)]);
 */
final class DelayAction implements Action
{
    /**
     * Build a delay action.
     *
     * @param int $ms Delay in milliseconds.
     * @param array<int, Action> $actions Actions to execute after delay.
     */
    public function __construct(
        public readonly int $ms,    
        public readonly mixed $actions
    ) {
        if($this->ms <= 0 ){
            throw new InvalidArgumentException("Delay must me a greater than zero integer!");
        }
        foreach($this->actions as $a){
            if( !$a instanceof Action){
                throw new InvalidArgumentException("$a needs to be instance of Action, " . gettype($a) . " give!");
            }
        }
    }

    /**
     * Return action discriminator.
     *
     * @return string
     */
    public function kind(): string { return 'delay'; }

    /**
     * Encode this action as runtime IR payload.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'k'         => $this->kind(), 
            'ms'        => $this->ms,
            'actions'   => $this->actions
        ];
    }
}

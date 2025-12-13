<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use InvalidArgumentException;

/**
 * Delay an action by a give interval
 * @param int $ms, miliseconds
 * @param Action[] $actions
 */
final class DelayAction implements Action
{
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

    public function kind(): string { return 'delay'; }

    public function jsonSerialize(): array
    {
        return [
            'k'         => $this->kind(), 
            'ms'        => $this->ms,
            'actions'   => $this->actions
        ];
    }
}

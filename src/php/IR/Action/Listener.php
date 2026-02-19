<?php
declare(strict_types=1);

namespace Thorm\IR\Action;

use JsonSerializable;
use \InvalidArgumentException;
use Thorm\IR\Atom;
use Thorm\IR\AtomCollectable;
use Thorm\IR\Expr\Expr;
use Thorm\IR\Expr\ExprVal;

/**
 * Legacy listener action wrapper used by props/event helpers.
 *
 * @group IR/Action
 * @example
 * $listener = Listener::inc($countAtom, 1);
 */
final class Listener implements \JsonSerializable, AtomCollectable
{
    /**
     * Build a listener instance.
     *
     * @param string $kind Listener kind discriminator.
     * @param Atom|null $atom Target atom.
     * @param mixed $payload Listener payload.
     */
    private function __construct(
        public string $kind,
        public ?Atom $atom = null,
        public mixed $payload = null
    ) {}

    /**
     * Collect atom dependencies referenced by this listener.
     *
     * @param callable $collect Collector callback.
     * @return void
     */
    public function collectAtoms(callable $collect): void
    {
        $collect($this->atom);
        // 'set' payload might be an Expr
        if (property_exists($this, 'payload') && $this->payload instanceof Expr) {
            $collect($this->payload);
        }
    }
    
    /**
     * Return listener discriminator.
     *
     * @return string
     */
    public function kind(): string
    {
        return $this->kind;
    }
    
    /**
     * Create an increment listener.
     *
     * @param Atom $atom Target atom.
     * @param int|float $by Increment delta.
     * @return self
     */
    public static function inc(Atom $atom, int|float $by): self {
        return new self('inc', $atom, $by);
    }

    /**
     * Create a set listener.
     *
     * @param Atom $atom Target atom.
     * @param Expr|int|float|string|bool $to Target value.
     * @return self
     */
    public static function set(Atom $atom, Expr|int|float|string|bool $to): self {
        $expr = $to instanceof Expr ? $to : new ExprVal($to);
        return new self('set', $atom, $expr);
    }

    /**
     * Create an add expression based on current atom value.
     *
     * @param Atom $atom Target atom.
     * @param Expr $by Addend expression.
     * @return Expr
     */
    public static function add(Atom $atom, Expr $by): Expr {
        return Expr::op('add', Expr::read($atom), $by); // payload is Expr
    }

    /**
     * Normalize scalar/expr inputs to Expr.
     *
     * @param mixed $x Input value.
     * @return Expr
     */
    private static function expr(mixed $x): Expr {
        return $x instanceof Expr ? $x : Expr::val($x);
    }

    /**
     * Create an HTTP listener payload.
     *
     * @param array<string, mixed> $opts HTTP options.
     * @return self
     */
    public static function http(array $opts): self {
        if (!isset($opts['url'])) {
            throw new \InvalidArgumentException("http(): 'url' is required");
        }

        // Optional atoms
        /** @var ?Atom $toAtom */
        $toAtom     = $opts['to']    ?? null;
        /** @var ?Atom $statusAtom */
        $statusAtom = $opts['status']?? null;

        if ($toAtom === null && $statusAtom === null) {
            throw new \InvalidArgumentException("http(): provide at least one of 'to' or 'status' Atom");
        }

        // Choose a primary atom to satisfy the constructor
        $primary = $toAtom ?? $statusAtom; // Atom

        // Normalize fields to Expr where needed
        $headers = [];
        foreach (($opts['headers'] ?? []) as $k => $v) {
            $headers[$k] = self::expr($v);
        }

        $payload = [
            'url'    => self::expr($opts['url']),
            'method' => strtoupper((string)($opts['method'] ?? 'GET')),
            'headers'=> $headers,
            'body'   => array_key_exists('body', $opts) ? self::expr($opts['body']) : null,
            'to'     => $toAtom?->id,
            'status' => $statusAtom?->id,
            'parse'  => $opts['parse'] ?? 'json',
        ];

        return new self('http', $primary, $payload);
    }

    /**
     * Listener::navigate
     *
     * Factory for a navigation action used inside on('click', navigate(...)).
     * - Normalizes $to (string|Expr) into an Expr for uniform IR.
     * - Produces IR: { k:'navigate', to: <Expr> }.
     * - Runtime handler: history.pushState + reroute().
     *
     * @param  Expr|string $to Target URL (can be a literal or an expression).
     * @return self            Listener instance with kind='navigate' and payload=Expr.
     */
    public static function navigate(Expr|string $to): self {
        $expr = $to instanceof Expr ? $to : Expr::val($to);
        $a = new self('navigate');
        $a->payload = $expr; // store as Expr
        return $a;
    }

    /**
     * Encode this listener as runtime IR payload.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed {
         $this->validate();
         return $this->toArray();
    }

    /**
     * Convert listener to normalized array payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return match ($this->kind) {
            'inc' => ['k'=>'inc','atom'=>$this->atom->id,'by'=>$this->payload],
            'set' => ['k'=>'set','atom'=>$this->atom->id,'to'=>$this->payload],
            'add' => ['k'=>'add','atom'=>$this->atom->id,'by'=>$this->payload],
            'http'=> ['k' => 'http'] + $this->payload,
            'navigate' => ['k'=>'navigate','to'=>$this->payload],
            default => ['k'=>'unknown'],
        };
    }

    /**
     * Validate listener state.
     *
     * @return void
     */
    public function validate(): void
    {
        if ($this->kind === '') {
            throw new InvalidArgumentException('Listener: missing kind "k".');
        }
    }

}

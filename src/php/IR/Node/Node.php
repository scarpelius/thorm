<?php
declare(strict_types=1);

namespace Thorm\IR\Node;

use JsonSerializable;

/**
 * Base IR node type.
 *
 * All IR node variants extend this class and must be JSON serializable
 * so they can be encoded and sent to the runtime.
 *
 * @group IR/Node
 * @example
 * final class MyNode extends Node {
 *     public function jsonSerialize(): mixed {
 *         return ['k' => 'my'];
 *     }
 * }
 */
abstract class Node implements JsonSerializable {}

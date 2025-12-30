<?php

namespace Thorm\IR;

interface AtomCollectable {
    public function collectAtoms(callable $collect):void;
}

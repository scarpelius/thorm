<?php

namespace Thorm\IR;

interface Renderable {
    public function render(callable $renderer):string;
}

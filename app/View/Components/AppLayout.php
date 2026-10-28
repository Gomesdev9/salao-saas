<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param  string|null  $title  Título da aba do navegador
     * @param  bool  $wide  Ocupa a largura toda (usado na agenda)
     */
    public function __construct(
        public ?string $title = null,
        public bool $wide = false,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}

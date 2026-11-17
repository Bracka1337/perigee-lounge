<?php

namespace App\Services\Render;

/**
 * Turns render studio pages into PNG files.
 */
interface Renderer
{
    /**
     * Opens every URL at the given size and saves a screenshot to its path.
     *
     * @param  list<array{url: string, path: string, width: int, height: int}>  $shots
     *
     * @throws \RuntimeException when a page cannot be rendered
     */
    public function render(array $shots): void;
}

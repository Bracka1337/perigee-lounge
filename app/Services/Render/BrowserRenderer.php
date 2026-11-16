<?php

namespace App\Services\Render;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Renders with a headless browser through scripts/render-studio.mjs (Node and Playwright).
 */
class BrowserRenderer implements Renderer
{
    public function render(array $shots): void
    {
        if ($shots === []) {
            return;
        }

        $manifest = tempnam(sys_get_temp_dir(), 'perigee-render');
        if ($manifest === false) {
            throw new RuntimeException('Could not create the render manifest.');
        }

        file_put_contents($manifest, json_encode($shots, JSON_THROW_ON_ERROR));

        try {
            $process = new Process(['node', base_path('scripts/render-studio.mjs'), $manifest], base_path());
            $process->setTimeout(120 + count($shots) * 20);
            $process->run();

            if (! $process->isSuccessful()) {
                $output = trim($process->getErrorOutput()) !== '' ? $process->getErrorOutput() : $process->getOutput();

                throw new RuntimeException('The render studio failed: '.trim($output));
            }
        } finally {
            @unlink($manifest);
        }
    }
}

<?php

namespace App\Services\Render;

use GdImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The local render studio: a page that draws one three.js scene from URL parameters,
 * and the headless browser that photographs it.
 */
class RenderStudio
{
    public const LOUNGE_SIZE = [1200, 800];

    public const MENU_SIZE = [1200, 900];

    public function __construct(private Renderer $renderer) {}

    /**
     * The studio exists only on a local machine, or where RENDER_STUDIO turns it on.
     */
    public function enabled(): bool
    {
        return app()->isLocal() || config('site.render_studio') === true;
    }

    /**
     * Renders studio scenes and hands each PNG to the callback, which stores it.
     *
     * @param  list<array{scene: string, id: int, size: array{0: int, 1: int}}>  $scenes
     * @param  callable(array{scene: string, id: int, size: array{0: int, 1: int}}, string): void  $store
     */
    public function shoot(string $rootUrl, array $scenes, callable $store): void
    {
        if ($scenes === []) {
            return;
        }

        $folder = storage_path('app/render-studio/'.Str::uuid()->toString());
        File::ensureDirectoryExists($folder);

        try {
            $shots = [];
            foreach ($scenes as $index => $scene) {
                $shots[] = [
                    'url' => $this->url($rootUrl, $scene['scene'], $scene['id']),
                    'path' => $folder.DIRECTORY_SEPARATOR.$index.'.png',
                    'width' => $scene['size'][0],
                    'height' => $scene['size'][1],
                ];
            }

            $this->renderer->render($shots);

            foreach ($scenes as $index => $scene) {
                if (! is_file($shots[$index]['path'])) {
                    throw new RuntimeException("The render studio produced no image for {$scene['scene']} {$scene['id']}.");
                }

                $store($scene, $shots[$index]['path']);
            }
        } finally {
            File::deleteDirectory($folder);
        }
    }

    /**
     * A signed link to one studio scene, so the headless browser gets in without a login.
     * The link is built for the address the app is actually served from.
     */
    public function url(string $rootUrl, string $scene, int $id): string
    {
        URL::useOrigin(rtrim($rootUrl, '/'));

        try {
            return URL::temporarySignedRoute('render-studio', now()->addHour(), ['scene' => $scene, 'id' => $id]);
        } finally {
            URL::useOrigin(null);
        }
    }

    /**
     * Re-encodes a PNG screenshot as WebP.
     */
    public function webp(string $pngPath): string
    {
        $contents = file_get_contents($pngPath);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if (! $image instanceof GdImage) {
            throw new RuntimeException("Could not read the rendered image {$pngPath}.");
        }

        ob_start();
        imagewebp($image, null, 82);

        return (string) ob_get_clean();
    }
}

<?php

namespace App\Actions\Render;

use App\Models\MenuItem;
use App\Services\MenuPhotoStore;
use App\Services\Render\RenderStudio;

class RenderMenuPhotos
{
    public function __construct(private RenderStudio $studio, private MenuPhotoStore $photos) {}

    /**
     * Renders a product shot for menu items and saves it like an uploaded photo
     * (a 1200 px copy and a 400 px thumbnail).
     *
     * An item with a photo that staff uploaded is never touched. An item that already has a
     * generated photo is rendered again only with $force.
     *
     * @return array{rendered: int, skipped: int}
     */
    public function handle(string $rootUrl, ?int $itemId = null, bool $force = false): array
    {
        $items = MenuItem::query()
            ->when($itemId !== null, fn ($query) => $query->whereKey($itemId))
            ->orderBy('id')
            ->get();

        $wanted = $items->filter(fn (MenuItem $item) => $this->mayRender($item, $force))->keyBy('id');

        $scenes = [];
        foreach ($wanted as $item) {
            $scenes[] = ['scene' => 'menu', 'id' => $item->id, 'size' => RenderStudio::MENU_SIZE];
        }

        $rendered = 0;

        $this->studio->shoot($rootUrl, $scenes, function (array $scene, string $png) use ($wanted, $force, &$rendered): void {
            // Staff may have uploaded a photo while the studio was working. Theirs wins.
            $item = $wanted->get($scene['id'])?->fresh();
            if ($item === null || ! $this->mayRender($item, $force)) {
                return;
            }

            $old = [$item->image_path, $item->thumb_path];

            $item->update([...$this->photos->storeFromPath($png, 'webp'), 'photo_generated' => true]);
            $this->photos->delete(...$old);
            $rendered++;
        });

        return ['rendered' => $rendered, 'skipped' => $items->count() - $rendered];
    }

    private function mayRender(MenuItem $item, bool $force): bool
    {
        if ($item->image_path === null || $item->image_path === '') {
            return true;
        }

        return $item->photo_generated && $force;
    }
}

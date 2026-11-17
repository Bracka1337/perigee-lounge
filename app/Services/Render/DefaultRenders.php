<?php

namespace App\Services\Render;

use App\Models\Floor;
use App\Models\MenuItem;
use App\Models\Room;
use App\Models\Table;
use App\Services\LoungeRenders;
use App\Services\MenuPhotoStore;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The default set of generated pictures that ships with the project, so a fresh install
 * shows room, table and menu images without a headless browser.
 *
 * Files are named after what they show (table name, floor and room name, category and item
 * name), because ids differ from one database to the next.
 */
class DefaultRenders
{
    public function __construct(private LoungeRenders $renders, private MenuPhotoStore $photos) {}

    public function folder(string $kind = ''): string
    {
        return database_path('seeders/renders'.($kind === '' ? '' : '/'.$kind));
    }

    /**
     * Copies the current room and table pictures into the default set.
     */
    public function exportLounge(): int
    {
        $disk = Storage::disk(LoungeRenders::DISK);
        $count = 0;

        foreach ($this->rooms() as [$room, $file]) {
            $count += $this->copyOut($disk->path($this->renders->roomPath($room->id)), $this->folder('rooms').'/'.$file);
        }

        foreach (Table::query()->orderBy('id')->get() as $table) {
            $count += $this->copyOut($disk->path($this->renders->tablePath($table->id)), $this->folder('tables').'/'.Str::slug($table->name).'.webp');
        }

        return $count;
    }

    /**
     * Copies the generated menu photos into the default set. Photos uploaded by staff stay out.
     */
    public function exportMenu(): int
    {
        $disk = Storage::disk(LoungeRenders::DISK);
        $count = 0;

        foreach (MenuItem::query()->with('category')->where('photo_generated', true)->whereNotNull('image_path')->get() as $item) {
            $count += $this->copyOut($disk->path((string) $item->image_path), $this->folder('menu').'/'.$this->menuFile($item));
        }

        return $count;
    }

    /**
     * Gives every room, table and menu item that has no picture yet the one from the default set.
     *
     * @return int how many pictures were installed
     */
    public function install(): int
    {
        $disk = Storage::disk(LoungeRenders::DISK);
        $count = 0;

        foreach ($this->rooms() as [$room, $file]) {
            $count += $this->copyIn($this->folder('rooms').'/'.$file, $this->renders->roomPath($room->id));
        }

        foreach (Table::query()->orderBy('id')->get() as $table) {
            $count += $this->copyIn($this->folder('tables').'/'.Str::slug($table->name).'.webp', $this->renders->tablePath($table->id));
        }

        foreach (MenuItem::query()->with('category')->whereNull('image_path')->get() as $item) {
            $source = $this->folder('menu').'/'.$this->menuFile($item);

            if (is_file($source)) {
                $item->update([...$this->photos->storeFromPath($source, 'webp'), 'photo_generated' => true]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return list<array{0: Room, 1: string}> every room with its file name in the default set
     */
    private function rooms(): array
    {
        $rooms = [];

        foreach (Floor::query()->with('rooms')->orderBy('sort_order')->orderBy('id')->get()->values() as $position => $floor) {
            foreach ($floor->rooms as $room) {
                $rooms[] = [$room, ($position + 1).'-'.Str::slug((string) ($room->name['en'] ?? $room->id)).'.webp'];
            }
        }

        return $rooms;
    }

    private function menuFile(MenuItem $item): string
    {
        return Str::slug((string) ($item->category->name['en'] ?? 'menu')).'-'.Str::slug((string) ($item->name['en'] ?? $item->id)).'.webp';
    }

    private function copyOut(string $from, string $to): int
    {
        if (! is_file($from)) {
            return 0;
        }

        File::ensureDirectoryExists(dirname($to));
        File::copy($from, $to);

        return 1;
    }

    private function copyIn(string $from, string $to): int
    {
        $disk = Storage::disk(LoungeRenders::DISK);

        if (! is_file($from) || $disk->exists($to)) {
            return 0;
        }

        $disk->put($to, (string) file_get_contents($from));

        return 1;
    }
}

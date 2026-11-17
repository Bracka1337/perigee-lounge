<?php

namespace App\Services;

use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Where the generated pictures of rooms and tables live on the public disk.
 */
class LoungeRenders
{
    public const DISK = 'public';

    public function roomPath(int $roomId): string
    {
        return "renders/rooms/{$roomId}.webp";
    }

    public function tablePath(int $tableId): string
    {
        return "renders/tables/{$tableId}.webp";
    }

    /**
     * The picture to show for each table: its own render, else its room's, else none.
     * The file time is added to the address so a regenerated picture is not served from cache.
     *
     * @param  Collection<int, Floor>  $floors  as loaded by LoungeLayout
     * @return array<int, string|null> table id => URL
     */
    public function urlsFor(Collection $floors): array
    {
        $disk = Storage::disk(self::DISK);
        $existing = array_flip([...$disk->files('renders/rooms'), ...$disk->files('renders/tables')]);

        $url = function (string $path) use ($disk, $existing): ?string {
            return isset($existing[$path]) ? $disk->url($path).'?v='.$disk->lastModified($path) : null;
        };

        $urls = [];
        foreach ($floors as $floor) {
            foreach ($floor->tables as $table) {
                $urls[$table->id] = $url($this->tablePath($table->id))
                    ?? ($table->room_id === null ? null : $url($this->roomPath($table->room_id)));
            }
        }

        return $urls;
    }

    /**
     * The picture of every room that has one.
     *
     * @param  Collection<int, Floor>  $floors
     * @return array<int, string> room id => URL
     */
    public function roomUrlsFor(Collection $floors): array
    {
        $disk = Storage::disk(self::DISK);
        $existing = array_flip($disk->files('renders/rooms'));

        $urls = [];
        foreach ($floors as $floor) {
            foreach ($floor->rooms as $room) {
                $path = $this->roomPath($room->id);
                if (isset($existing[$path])) {
                    $urls[$room->id] = $disk->url($path).'?v='.$disk->lastModified($path);
                }
            }
        }

        return $urls;
    }

    public function forget(Room|Table $model): void
    {
        Storage::disk(self::DISK)->delete(
            $model instanceof Room ? $this->roomPath($model->id) : $this->tablePath($model->id)
        );
    }
}

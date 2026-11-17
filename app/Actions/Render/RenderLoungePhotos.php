<?php

namespace App\Actions\Render;

use App\Models\Room;
use App\Models\Table;
use App\Services\LoungeRenders;
use App\Services\Render\RenderStudio;
use Illuminate\Support\Facades\Storage;

class RenderLoungePhotos
{
    public function __construct(private RenderStudio $studio, private LoungeRenders $renders) {}

    /**
     * Renders a picture of every room and every table, or of just the one asked for,
     * and stores them as WebP on the public disk.
     *
     * @return int how many pictures were written
     */
    public function handle(string $rootUrl, ?int $roomId = null, ?int $tableId = null): int
    {
        $everything = $roomId === null && $tableId === null;
        $scenes = [];

        $rooms = $everything ? Room::query()->orderBy('id')->pluck('id') : collect($roomId === null ? [] : [Room::query()->findOrFail($roomId)->id]);
        $tables = $everything ? Table::query()->orderBy('id')->pluck('id') : collect($tableId === null ? [] : [Table::query()->findOrFail($tableId)->id]);

        foreach ($rooms as $id) {
            $scenes[] = ['scene' => 'room', 'id' => (int) $id, 'size' => RenderStudio::LOUNGE_SIZE];
        }
        foreach ($tables as $id) {
            $scenes[] = ['scene' => 'table', 'id' => (int) $id, 'size' => RenderStudio::LOUNGE_SIZE];
        }

        $this->studio->shoot($rootUrl, $scenes, function (array $scene, string $png): void {
            $path = $scene['scene'] === 'room' ? $this->renders->roomPath($scene['id']) : $this->renders->tablePath($scene['id']);

            Storage::disk(LoungeRenders::DISK)->put($path, $this->studio->webp($png));
        });

        return count($scenes);
    }
}

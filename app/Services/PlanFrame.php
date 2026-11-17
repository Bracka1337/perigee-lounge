<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Table;

/**
 * The rectangle around the whole building, in metres, shared by every floor so they line up.
 * It turns metres into percentages of the drawn plan.
 */
final class PlanFrame
{
    /** Empty space drawn around the building, in metres. */
    public const MARGIN = 0.5;

    public function __construct(
        public readonly float $x,
        public readonly float $y,
        public readonly float $width,
        public readonly float $depth,
    ) {}

    /**
     * @param  iterable<Room>  $rooms
     * @param  iterable<Table>  $tables
     */
    public static function around(iterable $rooms, iterable $tables): self
    {
        $minX = $minY = INF;
        $maxX = $maxY = -INF;

        foreach ($rooms as $room) {
            $minX = min($minX, $room->x);
            $minY = min($minY, $room->y);
            $maxX = max($maxX, $room->x + $room->width);
            $maxY = max($maxY, $room->y + $room->height);
        }

        foreach ($tables as $table) {
            $halfWidth = $table->width / 200;
            $halfDepth = $table->height / 200;
            $minX = min($minX, $table->pos_x - $halfWidth);
            $minY = min($minY, $table->pos_y - $halfDepth);
            $maxX = max($maxX, $table->pos_x + $halfWidth);
            $maxY = max($maxY, $table->pos_y + $halfDepth);
        }

        // A lounge with nothing drawn yet still gets a sensible empty plan.
        if ($minX === INF) {
            return new self(0, 0, 30, 26);
        }

        return new self(
            $minX - self::MARGIN,
            $minY - self::MARGIN,
            max(4, $maxX - $minX + 2 * self::MARGIN),
            max(4, $maxY - $minY + 2 * self::MARGIN),
        );
    }

    /**
     * CSS for the plan box, so it keeps the building's proportions.
     */
    public function aspectRatio(): string
    {
        return $this->number($this->width).' / '.$this->number($this->depth);
    }

    /**
     * The SVG viewBox of the plan, in metres.
     */
    public function viewBox(): string
    {
        return implode(' ', array_map($this->number(...), [$this->x, $this->y, $this->width, $this->depth]));
    }

    /**
     * Inline CSS that places a room on the plan.
     */
    public function roomStyle(Room $room): string
    {
        return sprintf(
            'left: %s%%; top: %s%%; width: %s%%; height: %s%%;',
            $this->number(($room->x - $this->x) / $this->width * 100),
            $this->number(($room->y - $this->y) / $this->depth * 100),
            $this->number($room->width / $this->width * 100),
            $this->number($room->height / $this->depth * 100),
        );
    }

    /**
     * Inline CSS that places a table (centre, footprint and rotation) on the plan.
     */
    public function tableStyle(Table $table): string
    {
        return sprintf(
            'left: %s%%; top: %s%%; width: %s%%; height: %s%%; transform: translate(-50%%, -50%%) rotate(%ddeg);',
            $this->number(($table->pos_x - $this->x) / $this->width * 100),
            $this->number(($table->pos_y - $this->y) / $this->depth * 100),
            $this->number($table->width / 100 / $this->width * 100),
            $this->number($table->height / 100 / $this->depth * 100),
            $table->rotation,
        );
    }

    /**
     * @return array{x: float, y: float, width: float, depth: float}
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'width' => $this->width, 'depth' => $this->depth];
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }
}

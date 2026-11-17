<?php

namespace App\Enums;

/**
 * Things that can be placed in a room on the floor plan. Each type has a group for the editor
 * palette and a default footprint in metres.
 */
enum FixtureType: string
{
    // Entertainment
    case GameStation = 'game_station';
    case WallTv = 'wall_tv';
    case ProjectorScreen = 'projector_screen';
    case Karaoke = 'karaoke';
    case DjBooth = 'dj_booth';
    case Speaker = 'speaker';

    // Bar and service
    case BarCounter = 'bar_counter';
    case HookahStation = 'hookah_station';
    case CoatRack = 'coat_rack';

    // Decor
    case Plant = 'plant';
    case Partition = 'partition';
    case Curtain = 'curtain';
    case Aquarium = 'aquarium';
    case FeatureWall = 'feature_wall';

    // Lights
    case PendantLight = 'pendant_light';
    case FloorLamp = 'floor_lamp';
    case NeonStrip = 'neon_strip';
    case LedStrip = 'led_strip';

    /**
     * The colours a light can have, as hex values.
     */
    public const LIGHT_COLORS = [
        'amber' => '#F5B860',
        'violet' => '#B9A5FF',
        'rose' => '#F28BB0',
        'teal' => '#5ED6C4',
        'white' => '#FFF1D6',
    ];

    public function group(): string
    {
        return match ($this) {
            self::GameStation, self::WallTv, self::ProjectorScreen, self::Karaoke, self::DjBooth, self::Speaker => 'entertainment',
            self::BarCounter, self::HookahStation, self::CoatRack => 'bar',
            self::Plant, self::Partition, self::Curtain, self::Aquarium, self::FeatureWall => 'decor',
            self::PendantLight, self::FloorLamp, self::NeonStrip, self::LedStrip => 'lights',
        };
    }

    /**
     * Default width and depth in metres.
     *
     * @return array{0: float, 1: float}
     */
    public function size(): array
    {
        return match ($this) {
            self::GameStation => [1.8, 0.6],
            self::WallTv => [1.6, 0.15],
            self::ProjectorScreen => [4.0, 0.2],
            self::Karaoke => [2.4, 0.8],
            self::DjBooth => [2.0, 0.9],
            self::Speaker => [0.5, 0.5],
            self::BarCounter => [0.6, 0.6], // the real shape comes from its points
            self::HookahStation => [1.6, 0.7],
            self::CoatRack => [1.2, 0.5],
            self::Plant => [0.7, 0.7],
            self::Partition => [2.0, 0.12],
            self::Curtain => [2.4, 0.15],
            self::Aquarium => [2.0, 0.6],
            self::FeatureWall => [3.0, 0.2],
            self::PendantLight => [0.5, 0.5],
            self::FloorLamp => [0.5, 0.5],
            self::NeonStrip => [2.0, 0.1],
            self::LedStrip => [3.0, 0.1],
        };
    }

    public function isLight(): bool
    {
        return $this->group() === 'lights';
    }

    public function labelKey(): string
    {
        return 'fixture-'.str_replace('_', '-', $this->value);
    }
}

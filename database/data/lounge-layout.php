<?php

/**
 * The default layout of the lounge: two floors, their rooms and their tables.
 *
 * Rooms are rectangles in percent of the plan, measured from the top-left corner.
 * Tables are [name, capacity, centre x %, centre y %, width px, height px], with the
 * sizes in pixels of a plan that is 1200 px wide and 1050 px high.
 *
 * Both floors share one structure: a side strip on the left, a walkway between the
 * halls and the right-hand rooms, and stairs and restrooms in the same place.
 */
$name = fn (string $en, string $lv, string $ru): array => ['en' => $en, 'lv' => $lv, 'ru' => $ru];

return [
    [
        'name' => $name('Ground floor', 'Pirmais stāvs', 'Первый этаж'),
        'rooms' => [
            [
                'name' => $name('Terrace', 'Terase', 'Терраса'),
                'kind' => 'seating', 'color' => 'lime',
                'x' => 0, 'y' => 0, 'width' => 18, 'height' => 100,
                'tables' => [
                    ['B1', 4, 5, 12, 90, 80],
                    ['B2', 4, 5, 36, 90, 80],
                    ['B3', 4, 5, 60, 90, 80],
                    ['B4', 4, 5, 84, 90, 80],
                    ['B5', 4, 13, 24, 90, 80],
                    ['B6', 4, 13, 48, 90, 80],
                    ['B7', 4, 13, 72, 90, 80],
                ],
            ],
            [
                'name' => $name('Main hall', 'Lielā zāle', 'Основной зал'),
                'kind' => 'seating', 'color' => 'sky',
                'x' => 20, 'y' => 0, 'width' => 42, 'height' => 40,
                'tables' => [
                    ['T1', 4, 28.5, 10, 150, 80],
                    ['T2', 4, 43, 10, 150, 80],
                    ['T7', 3, 56, 10, 100, 80],
                    ['T3', 6, 31.5, 21, 220, 80],
                    ['T5', 4, 53.5, 21, 140, 80],
                    ['T4', 6, 31.5, 32, 220, 80],
                    ['T6', 4, 53.5, 32, 140, 80],
                ],
            ],
            [
                'name' => $name('Bar', 'Bārs', 'Бар'),
                'kind' => 'bar', 'color' => 'amber',
                'x' => 68, 'y' => 0, 'width' => 32, 'height' => 24,
            ],
            [
                'name' => $name('Game room', 'Spēļu istaba', 'Игровая'),
                'kind' => 'seating', 'color' => 'rose',
                'x' => 68, 'y' => 28, 'width' => 32, 'height' => 26,
                'tables' => [
                    ['P1', 4, 77, 42, 80, 120],
                    ['P2', 4, 91, 42, 80, 120],
                ],
            ],
            [
                'name' => $name('Lounge hall', 'Lounge zāle', 'Лаунж-зал'),
                'kind' => 'seating', 'color' => 'violet',
                'x' => 20, 'y' => 46, 'width' => 42, 'height' => 42,
                'tables' => [
                    ['Z1', 4, 27, 54, 140, 80],
                    ['Z5', 4, 40.5, 54, 140, 80],
                    ['Z6', 4, 27, 66, 140, 80],
                    ['Z7', 4, 40.5, 66, 140, 80],
                    ['Z8', 4, 27, 78, 140, 80],
                    ['Z2', 4, 57, 56.5, 90, 160],
                    ['Z4', 2, 57, 70.5, 90, 90],
                    ['Z3', 2, 57, 82, 90, 90],
                ],
            ],
            [
                'name' => $name('VIP', 'VIP', 'VIP'),
                'kind' => 'seating', 'color' => 'coral',
                'x' => 68, 'y' => 58, 'width' => 32, 'height' => 30,
                'tables' => [
                    ['VIP', 20, 84, 73.5, 210, 260],
                ],
            ],
            [
                'name' => $name('Cloakroom', 'Garderobe', 'Гардероб'),
                'kind' => 'service', 'color' => 'slate',
                'x' => 20, 'y' => 90, 'width' => 14, 'height' => 10,
            ],
            [
                'name' => $name('Entrance', 'Ieeja', 'Вход'),
                'kind' => 'entrance', 'color' => 'slate',
                'x' => 36, 'y' => 90, 'width' => 18, 'height' => 10,
            ],
            [
                'name' => $name('Stairs', 'Kāpnes', 'Лестница'),
                'kind' => 'stairs', 'color' => 'slate',
                'x' => 56, 'y' => 90, 'width' => 10, 'height' => 10,
            ],
            [
                'name' => $name('Restrooms', 'Tualetes', 'Туалеты'),
                'kind' => 'service', 'color' => 'slate',
                'x' => 68, 'y' => 90, 'width' => 32, 'height' => 10,
            ],
        ],
    ],
    [
        'name' => $name('Second floor', 'Otrais stāvs', 'Второй этаж'),
        'rooms' => [
            [
                'name' => $name('Balcony', 'Balkons', 'Балкон'),
                'kind' => 'seating', 'color' => 'lime',
                'x' => 0, 'y' => 0, 'width' => 18, 'height' => 100,
                'tables' => [
                    ['L1', 2, 9, 14, 90, 90],
                    ['L2', 4, 9, 38, 100, 80],
                    ['L3', 4, 9, 62, 100, 80],
                    ['L4', 2, 9, 86, 90, 90],
                ],
            ],
            [
                'name' => $name('Cinema room', 'Kino zāle', 'Кинозал'),
                'kind' => 'seating', 'color' => 'violet',
                'x' => 20, 'y' => 0, 'width' => 42, 'height' => 38,
                'tables' => [
                    ['C1', 4, 31, 16, 160, 80],
                    ['C2', 4, 51, 16, 160, 80],
                    ['C3', 4, 41, 29, 160, 80],
                ],
            ],
            [
                'name' => $name('Karaoke room', 'Karaoke istaba', 'Караоке'),
                'kind' => 'seating', 'color' => 'rose',
                'x' => 20, 'y' => 44, 'width' => 42, 'height' => 32,
                'tables' => [
                    ['K1', 8, 34, 61, 240, 120],
                    ['K2', 4, 53, 61, 140, 80],
                ],
            ],
            [
                'name' => $name('Upstairs bar', 'Augšstāva bārs', 'Бар на втором этаже'),
                'kind' => 'bar', 'color' => 'amber',
                'x' => 68, 'y' => 0, 'width' => 32, 'height' => 24,
                'tables' => [
                    ['U1', 2, 77, 15, 80, 80],
                    ['U2', 2, 91, 15, 80, 80],
                ],
            ],
            [
                'name' => $name('Cabin 1', 'Kabīne 1', 'Кабина 1'),
                'kind' => 'seating', 'color' => 'teal',
                'x' => 68, 'y' => 28, 'width' => 15, 'height' => 29,
                'tables' => [
                    ['S1', 4, 75.5, 43.5, 130, 150],
                ],
            ],
            [
                'name' => $name('Cabin 2', 'Kabīne 2', 'Кабина 2'),
                'kind' => 'seating', 'color' => 'teal',
                'x' => 85, 'y' => 28, 'width' => 15, 'height' => 29,
                'tables' => [
                    ['S2', 4, 92.5, 43.5, 130, 150],
                ],
            ],
            [
                'name' => $name('Cabin 3', 'Kabīne 3', 'Кабина 3'),
                'kind' => 'seating', 'color' => 'teal',
                'x' => 68, 'y' => 59, 'width' => 15, 'height' => 29,
                'tables' => [
                    ['S3', 6, 75.5, 74.5, 130, 150],
                ],
            ],
            [
                'name' => $name('Cabin 4', 'Kabīne 4', 'Кабина 4'),
                'kind' => 'seating', 'color' => 'teal',
                'x' => 85, 'y' => 59, 'width' => 15, 'height' => 29,
                'tables' => [
                    ['S4', 6, 92.5, 74.5, 130, 150],
                ],
            ],
            [
                'name' => $name('Stairs', 'Kāpnes', 'Лестница'),
                'kind' => 'stairs', 'color' => 'slate',
                'x' => 56, 'y' => 90, 'width' => 10, 'height' => 10,
            ],
            [
                'name' => $name('Restrooms', 'Tualetes', 'Туалеты'),
                'kind' => 'service', 'color' => 'slate',
                'x' => 68, 'y' => 90, 'width' => 32, 'height' => 10,
            ],
        ],
    ],
];

<?php

/**
 * The default layout of the lounge: two floors, their rooms and their tables, in metres.
 *
 * Rooms are rectangles [x, y, width, height] measured from the top-left corner of the building.
 * Rooms touch but never overlap, so the floor has no gaps: walkways are rooms of kind "corridor".
 * Tables are [name, capacity, centre x m, centre y m, width cm, depth cm]. The width and depth
 * are the footprint of the table with its seats.
 *
 * Stairs and restrooms sit in the same place on both floors.
 */
$name = fn (string $en, string $lv, string $ru): array => ['en' => $en, 'lv' => $lv, 'ru' => $ru];

return [
    [
        'name' => $name('Ground floor', 'Pirmais stāvs', 'Первый этаж'),
        'rooms' => [
            [
                'name' => $name('Terrace', 'Terase', 'Терраса'),
                'kind' => 'seating', 'color' => 'lime',
                'x' => 0, 'y' => 0, 'width' => 6, 'height' => 26,
                'tables' => [
                    ['B1', 4, 1.67, 3.12, 225, 200],
                    ['B2', 4, 1.67, 9.36, 225, 200],
                    ['B3', 4, 1.67, 15.6, 225, 200],
                    ['B4', 4, 1.67, 21.84, 225, 200],
                    ['B5', 4, 4.33, 6.24, 225, 200],
                    ['B6', 4, 4.33, 12.48, 225, 200],
                    ['B7', 4, 4.33, 18.72, 225, 200],
                ],
            ],
            [
                'name' => $name('Main hall', 'Lielā zāle', 'Основной зал'),
                'kind' => 'seating', 'color' => 'sky',
                'x' => 6, 'y' => 0, 'width' => 13, 'height' => 10.5,
                'tables' => [
                    ['T1', 4, 8.63, 2.63, 375, 200],
                    ['T2', 4, 13.12, 2.63, 375, 200],
                    ['T7', 3, 17.14, 2.63, 250, 200],
                    ['T3', 6, 9.56, 5.51, 550, 200],
                    ['T5', 4, 16.37, 5.51, 350, 200],
                    ['T4', 6, 9.56, 8.4, 550, 200],
                    ['T6', 4, 16.37, 8.4, 350, 200],
                ],
            ],
            [
                'name' => $name('Bar', 'Bārs', 'Бар'),
                'kind' => 'bar', 'color' => 'amber',
                'x' => 21, 'y' => 0, 'width' => 9, 'height' => 6.5,
            ],
            [
                'name' => $name('Game room', 'Spēļu istaba', 'Игровая'),
                'kind' => 'seating', 'color' => 'rose',
                'x' => 21, 'y' => 6.5, 'width' => 9, 'height' => 7,
                'tables' => [
                    ['P1', 4, 23.53, 10.27, 200, 300],
                    ['P2', 4, 27.47, 10.27, 200, 300],
                ],
            ],
            [
                'name' => $name('Lounge hall', 'Lounge zāle', 'Лаунж-зал'),
                'kind' => 'seating', 'color' => 'violet',
                'x' => 6, 'y' => 12, 'width' => 13, 'height' => 9,
                'tables' => [
                    ['Z1', 4, 8.17, 14.2, 350, 200],
                    ['Z5', 4, 12.35, 14.2, 350, 200],
                    ['Z6', 4, 8.17, 16.6, 350, 200],
                    ['Z7', 4, 12.35, 16.6, 350, 200],
                    ['Z8', 4, 8.17, 19, 350, 200],
                    ['Z2', 4, 17.45, 14.7, 225, 400],
                    ['Z4', 2, 17.45, 17.4, 225, 225],
                    ['Z3', 2, 17.45, 19.75, 225, 225],
                ],
            ],
            [
                'name' => $name('VIP', 'VIP', 'VIP'),
                'kind' => 'seating', 'color' => 'coral',
                'x' => 21, 'y' => 13.5, 'width' => 9, 'height' => 7.5,
                'tables' => [
                    ['VIP', 20, 25.5, 17.38, 525, 650],
                ],
            ],
            [
                'name' => $name('Cloakroom', 'Garderobe', 'Гардероб'),
                'kind' => 'service', 'color' => 'slate',
                'x' => 6, 'y' => 21, 'width' => 4, 'height' => 5,
            ],
            [
                'name' => $name('Entrance', 'Ieeja', 'Вход'),
                'kind' => 'entrance', 'color' => 'slate',
                'x' => 10, 'y' => 21, 'width' => 5, 'height' => 5,
            ],
            [
                'name' => $name('Stairs', 'Kāpnes', 'Лестница'),
                'kind' => 'stairs', 'color' => 'slate',
                'x' => 15, 'y' => 21, 'width' => 4, 'height' => 5,
            ],
            [
                'name' => $name('Restrooms', 'Tualetes', 'Туалеты'),
                'kind' => 'service', 'color' => 'slate',
                'x' => 21, 'y' => 21, 'width' => 9, 'height' => 5,
            ],
            [
                'name' => $name('Walkway', 'Gaitenis', 'Проход'),
                'kind' => 'corridor', 'color' => 'slate',
                'x' => 6, 'y' => 10.5, 'width' => 13, 'height' => 1.5,
            ],
            [
                'name' => $name('Main walkway', 'Galvenais gaitenis', 'Главный проход'),
                'kind' => 'corridor', 'color' => 'slate',
                'x' => 19, 'y' => 0, 'width' => 2, 'height' => 26,
            ],
        ],
    ],
    [
        'name' => $name('Second floor', 'Otrais stāvs', 'Второй этаж'),
        'rooms' => [
            [
                'name' => $name('Balcony', 'Balkons', 'Балкон'),
                'kind' => 'seating', 'color' => 'lime',
                'x' => 0, 'y' => 0, 'width' => 6, 'height' => 26,
                'tables' => [
                    ['L1', 2, 3, 3.64, 225, 225],
                    ['L2', 4, 3, 9.88, 250, 200],
                    ['L3', 4, 3, 16.12, 250, 200],
                    ['L4', 2, 3, 22.36, 225, 225],
                ],
            ],
            [
                'name' => $name('Cinema room', 'Kino zāle', 'Кинозал'),
                'kind' => 'seating', 'color' => 'violet',
                'x' => 6, 'y' => 0, 'width' => 13, 'height' => 10,
                'tables' => [
                    ['C1', 4, 9.4, 4.21, 400, 200],
                    ['C2', 4, 15.6, 4.21, 400, 200],
                    ['C3', 4, 12.5, 7.63, 400, 200],
                ],
            ],
            [
                'name' => $name('Karaoke room', 'Karaoke istaba', 'Караоке'),
                'kind' => 'seating', 'color' => 'rose',
                'x' => 6, 'y' => 11.5, 'width' => 13, 'height' => 9.5,
                'tables' => [
                    ['K1', 8, 10.33, 16.55, 600, 300],
                    ['K2', 4, 16.21, 16.55, 350, 200],
                ],
            ],
            [
                'name' => $name('Upstairs bar', 'Augšstāva bārs', 'Бар на втором этаже'),
                'kind' => 'bar', 'color' => 'amber',
                'x' => 21, 'y' => 0, 'width' => 9, 'height' => 6.5,
                'tables' => [
                    ['U1', 2, 23.53, 4.06, 200, 200],
                    ['U2', 2, 27.47, 4.06, 200, 200],
                ],
            ],
            [
                'name' => $name('Cabin 1', 'Kabīne 1', 'Кабина 1'),
                'kind' => 'seating', 'color' => 'teal',
                'x' => 21, 'y' => 6.5, 'width' => 4.5, 'height' => 7.25,
                'tables' => [
                    ['S1', 4, 23.25, 10.38, 325, 375],
                ],
            ],
            [
                'name' => $name('Cabin 2', 'Kabīne 2', 'Кабина 2'),
                'kind' => 'seating', 'color' => 'teal',
                'x' => 25.5, 'y' => 6.5, 'width' => 4.5, 'height' => 7.25,
                'tables' => [
                    ['S2', 4, 27.75, 10.38, 325, 375],
                ],
            ],
            [
                'name' => $name('Cabin 3', 'Kabīne 3', 'Кабина 3'),
                'kind' => 'seating', 'color' => 'teal',
                'x' => 21, 'y' => 13.75, 'width' => 4.5, 'height' => 7.25,
                'tables' => [
                    ['S3', 6, 23.25, 17.63, 325, 375],
                ],
            ],
            [
                'name' => $name('Cabin 4', 'Kabīne 4', 'Кабина 4'),
                'kind' => 'seating', 'color' => 'teal',
                'x' => 25.5, 'y' => 13.75, 'width' => 4.5, 'height' => 7.25,
                'tables' => [
                    ['S4', 6, 27.75, 17.63, 325, 375],
                ],
            ],
            [
                'name' => $name('Stairs', 'Kāpnes', 'Лестница'),
                'kind' => 'stairs', 'color' => 'slate',
                'x' => 15, 'y' => 21, 'width' => 4, 'height' => 5,
            ],
            [
                'name' => $name('Restrooms', 'Tualetes', 'Туалеты'),
                'kind' => 'service', 'color' => 'slate',
                'x' => 21, 'y' => 21, 'width' => 9, 'height' => 5,
            ],
            [
                'name' => $name('Walkway', 'Gaitenis', 'Проход'),
                'kind' => 'corridor', 'color' => 'slate',
                'x' => 6, 'y' => 10, 'width' => 13, 'height' => 1.5,
            ],
            [
                'name' => $name('Main walkway', 'Galvenais gaitenis', 'Главный проход'),
                'kind' => 'corridor', 'color' => 'slate',
                'x' => 19, 'y' => 0, 'width' => 2, 'height' => 26,
            ],
            [
                'name' => $name('Landing', 'Kāpņu laukums', 'Лестничная площадка'),
                'kind' => 'corridor', 'color' => 'slate',
                'x' => 6, 'y' => 21, 'width' => 9, 'height' => 5,
            ],
        ],
    ],
];

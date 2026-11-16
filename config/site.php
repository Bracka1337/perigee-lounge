<?php

return [
    'locales' => ['en', 'lv', 'ru'],

    'default_locale' => 'en',

    // The render studio draws the generated room, table and menu pictures. It is always on
    // when APP_ENV is local. Set RENDER_STUDIO=true to turn it on elsewhere.
    'render_studio' => (bool) env('RENDER_STUDIO', false),
];

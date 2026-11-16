<?php

namespace App\Console\Commands;

use App\Actions\Render\RenderLoungePhotos;
use App\Services\Render\DefaultRenders;
use App\Services\Render\RenderStudio;
use Illuminate\Console\Command;
use RuntimeException;

class RenderLounge extends Command
{
    protected $signature = 'perigee:render-lounge
        {--room= : Render only this room id}
        {--table= : Render only this table id}
        {--url= : Address the app is served from, if not APP_URL}
        {--export : Also copy the pictures to database/seeders/renders as the default set}';

    protected $description = 'Render a 3D picture of every room and table of the lounge (needs Node and Chrome)';

    public function handle(RenderStudio $studio, RenderLoungePhotos $render, DefaultRenders $defaults): int
    {
        if (! $studio->enabled()) {
            $this->error('The render studio is off. It runs when APP_ENV is local or RENDER_STUDIO=true.');

            return self::FAILURE;
        }

        $room = $this->option('room');
        $table = $this->option('table');

        try {
            $count = $render->handle(
                $this->rootUrl(),
                is_numeric($room) ? (int) $room : null,
                is_numeric($table) ? (int) $table : null,
            );
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            $this->line('Is the app running at '.$this->rootUrl().'? Pass --url= if it is served elsewhere.');

            return self::FAILURE;
        }

        $this->info("Rendered {$count} lounge pictures.");

        if ($this->option('export') === true) {
            $this->info('Exported '.$defaults->exportLounge().' pictures to database/seeders/renders.');
        }

        return self::SUCCESS;
    }

    private function rootUrl(): string
    {
        $url = $this->option('url');

        return is_string($url) && $url !== '' ? $url : (string) config('app.url');
    }
}

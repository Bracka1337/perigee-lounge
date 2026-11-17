<?php

namespace App\Console\Commands;

use App\Actions\Render\RenderMenuPhotos;
use App\Services\Render\DefaultRenders;
use App\Services\Render\RenderStudio;
use Illuminate\Console\Command;
use RuntimeException;

class RenderMenu extends Command
{
    protected $signature = 'perigee:render-menu
        {--item= : Render only this menu item id}
        {--force : Render again items that already have a generated photo}
        {--url= : Address the app is served from, if not APP_URL}
        {--export : Also copy the generated photos to database/seeders/renders as the default set}';

    protected $description = 'Render a 3D product shot for menu items without a photo. Photos uploaded by staff are never replaced';

    public function handle(RenderStudio $studio, RenderMenuPhotos $render, DefaultRenders $defaults): int
    {
        if (! $studio->enabled()) {
            $this->error('The render studio is off. It runs when APP_ENV is local or RENDER_STUDIO=true.');

            return self::FAILURE;
        }

        $item = $this->option('item');

        try {
            $result = $render->handle($this->rootUrl(), is_numeric($item) ? (int) $item : null, $this->option('force') === true);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            $this->line('Is the app running at '.$this->rootUrl().'? Pass --url= if it is served elsewhere.');

            return self::FAILURE;
        }

        $this->info("Rendered {$result['rendered']} menu photos, skipped {$result['skipped']}.");

        if ($this->option('export') === true) {
            $this->info('Exported '.$defaults->exportMenu().' photos to database/seeders/renders.');
        }

        return self::SUCCESS;
    }

    private function rootUrl(): string
    {
        $url = $this->option('url');

        return is_string($url) && $url !== '' ? $url : (string) config('app.url');
    }
}

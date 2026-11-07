<?php

namespace App\Translation;

use App\Models\Translation;
use Illuminate\Translation\Translator;

class DatabaseTranslator extends Translator
{
    /**
     * @param  array<array-key, mixed>  $replace
     * @return array<string, mixed>|string
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $locale = $locale ?? $this->locale;

        $dbValue = Translation::getValue($key, $locale);

        if ($dbValue !== null) {
            return $this->makeReplacements($dbValue, $replace);
        }

        return parent::get($key, $replace, $locale, $fallback);
    }
}

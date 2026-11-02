<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TranslationController extends Controller
{
    public function index(Request $request): View|string
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        $query = Translation::query();

        $search = $request->get('q');
        if ($search !== null && $search !== '' && $search !== false) {
            $query->where(function ($q) use ($search, $locales) {
                $q->where('key', 'like', "%{$search}%");

                foreach ($locales as $locale) {
                    $q->orWhere("value->$locale", 'like', "%{$search}%");
                }
            });
        }

        $translations = $query
            ->orderBy('key')
            ->paginate(30)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.translations.partials.list', compact('translations', 'locales', 'default'))
                ->render();
        }

        return view('admin.translations.index', compact('translations', 'locales', 'default'));
    }

    public function create(): View
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        return view('admin.translations.create', compact('locales', 'default'));
    }

    public function store(Request $request): RedirectResponse
    {
        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        $data = $request->validate([
            'key' => ['required', 'string', 'max:255', 'unique:translations,key'],
            'value' => ['required', 'array'],
            "value.$default" => ['required', 'string'],
            'value.*' => ['nullable', 'string'],
        ]);

        $translation = Translation::create([
            'key' => $data['key'],
            'value' => $data['value'],
        ]);

        Translation::forgetCache($translation->key);

        return redirect()
            ->route('admin.translations.index')
            ->with('status', __('admin-translations-status-created'));
    }

    public function edit(string $locale, int $id): View
    {
        $translation = Translation::findOrFail($id);

        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        return view('admin.translations.edit', compact('translation', 'locales', 'default'));
    }

    public function update(string $locale, Request $request, int $id): RedirectResponse
    {
        $translation = Translation::findOrFail($id);

        $locales = config('site.locales', ['ru']);
        $default = config('site.default_locale', $locales[0] ?? 'ru');

        $data = $request->validate([
            'key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('translations', 'key')->ignore($translation->id),
            ],
            'value' => ['required', 'array'],
            "value.$default" => ['required', 'string'],
            'value.*' => ['nullable', 'string'],
        ]);

        $oldKey = $translation->key;

        $translation->update([
            'key' => $data['key'],
            'value' => $data['value'],
        ]);

        Translation::forgetCache($oldKey);
        Translation::forgetCache($translation->key);

        return redirect()
            ->route('admin.translations.index', ['locale' => $locale])
            ->with('status', __('admin-translations-status-updated'));
    }

    public function destroy(string $locale, int $id): RedirectResponse
    {
        $translation = Translation::findOrFail($id);

        $key = $translation->key;

        $translation->delete();
        Translation::forgetCache($key);

        return redirect()
            ->route('admin.translations.index', ['locale' => $locale])
            ->with('status', __('admin-translations-status-deleted'));
    }
}

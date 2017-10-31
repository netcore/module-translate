<?php
use Netcore\Translator\Models\Language;

if (!function_exists('languages')) {
    function languages()
    {
        return cache()->rememberForever('languages', function () {
            return \Netcore\Translator\Models\Language::visible()->get();

        });
    }
}

if (!function_exists('set_language')) {
    /**
     * @param Language $language
     * @return bool
     */
    function set_language(Language $language)
    {
        app()->setLocale($language->iso_code);
        session()->put('locale', $language->iso_code);

        return true;
    }
}
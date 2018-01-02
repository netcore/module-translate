<?php

namespace Modules\Translate\Traits;

trait SyncTranslations
{

    /**
     * Store translations
     *
     * @param array $values
     */
    public function storeTranslations($values)
    {
        $array = [];

        foreach ($values as $lang => $data) {
            $array[$lang]['locale'] = $lang;

            foreach ($this->translatedAttributes as $attribute) {
                $array[$lang][$attribute] = isset($values[$lang][$attribute]) ? $values[$lang][$attribute] : '';
            }
        }

        $this->translations()->createMany($array);
    }

    /**
     * Update translations
     *
     * @param array $values
     */
    public function updateTranslations(array $values)
    {
        $array = [];

        foreach ($values as $lang => $data) {
            $array['locale'] = $lang;

            foreach ($this->translatedAttributes as $key => $attribute) {
                $array[$attribute] = isset($values[$lang][$attribute]) ? $values[$lang][$attribute] : '';
            }

            $translation = $this->translations()->where('locale', $lang)->first();

            // Make sure we can translate Stapler files as well
            $staplerConfig = (array)object_get(new $this->translationModel(), 'staplerConfig', []);
            $staplerFields = array_keys($staplerConfig);
            foreach ($staplerFields as $staplerField) {
                $array = collect($array)->filter(function ($value, $key) use ($staplerField) {

                    $removable = [
                        $staplerField . '_file_name',
                        $staplerField . '_file_size',
                        $staplerField . '_content_type',
                        $staplerField . '_updated_at'
                    ];

                    if (in_array($key, $removable)) {
                        return false;
                    }

                    return true;
                })->toArray();
            }

            if (! $translation) {
                $this->translations()->create($array);
            } else {
                $translation->update($array);
            }
        }
    }

}

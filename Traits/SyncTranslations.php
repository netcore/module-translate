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
                $array[$lang][$attribute] = isset($values[$lang][$attribute]) ? $values[$lang][$attribute] : null;
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
                $array[$attribute] = isset($values[$lang][$attribute]) ? $values[$lang][$attribute] : null;
            }

            $translation = $this->translations()->where('locale', $lang)->first();
            if (!$translation) {
                $translation = $this->translations()->create($array);
            } else {
                $translation->fill($array);
                $translation->save();
            }
        }
    }

}

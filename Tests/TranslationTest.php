<?php

namespace Modules\Translate\Tests;

use Netcore\Translator\Models\Translation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TranslationTest extends TestCase
{
    /**
     * @var array
     */
    private $translations = [
        'test-1' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        'test-2' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        'test-3' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        'test-4' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        'test-5' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        'test-6' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        'test-7' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
        'test-8' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
    ];


    public function testLgHelperMethod()
    {
        foreach ($this->translations as $key => $value) {
            $test = lg($key, $value);

            $this->assertTrue(str_contains($test, $key) || str_contains($test, $value));
        }

        Translation::whereIn('key', array_keys($this->translations))->delete();
    }

    public function testTransHelperMethod()
    {
        foreach ($this->translations as $key => $value) {
            $test = trans($key);

            $this->assertTrue(str_contains($test, $key) || str_contains($test, $value));
        }
    }
}

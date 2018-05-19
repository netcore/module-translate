<?php

namespace Modules\Translate\Console;

use Illuminate\Console\Command;
use Netcore\Translator\Helpers\TransHelper;
use Netcore\Translator\Models\Language;
use Symfony\Component\Finder\Finder;

class FindTranslations extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translations:find';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find translations in project';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $paths = [
            base_path('app'),
            base_path('modules'),
            base_path('resources'),
            base_path('routes')
        ];
        $translations = [];

        // Default laravel translations
        $files = \File::allFiles(resource_path('lang/en'));
        foreach ($files as $file) {
            $fullPath = $file->getPathname();
            $group = str_replace('.php', '', $file->getFilename());
            foreach (\File::getRequire($fullPath) as $key => $translation) {
                foreach ($this->makeRows($group, $key, $translation) as $row) {
                    if ($row['key'] == 'validation.custom.attribute-name') {
                        continue;
                    }
                    $translations[] = $row;
                }
            }
        }

        // Find translations in files
        $finder = new Finder();
        $finder->in($paths)->name('*.php')->files();

        foreach ($finder as $file) {
            if (preg_match_all('/lg(\(((?:[^()]*|(?-2))*)\))/', $file->getContents(), $matches)) {
                foreach ($matches[2] as $i => $match) {
                    $value = explode(',', $match);
                    $key = $value[0];

                    if (count($value) > 2) {
                        $val = isset($value[4]) && isset($value[5]) ? $value[4] . $value[5] : (isset($value[3]) ? $value[3] : $value[2]);
                    } else {
                        $val = isset($value[1]) ? $value[1] : '';
                    }

                    $key = str_replace("'", '', $key);
                    $val = preg_replace("/[,']+/", '', trim($val));
                    $val = preg_replace('!\s+!', ' ', $val);

                    $translations[] = [
                        'key'   => $key,
                        'value' => $val
                    ];
                }
            }
        }

        // Remove duplicate keys
        $translations = array_filter($translations, function ($value, $key) use ($translations) {
            return $key === array_search($value['key'], array_column($translations, 'key'));
        }, ARRAY_FILTER_USE_BOTH);

        // Set value for each language
        $translations = array_map(function ($translation) {
            $data = [
                'key' => $translation['key']
            ];

            foreach (TransHelper::getAllLanguages() as $language) {
                $data[$language->iso_code] = $translation['value'];
            }

            return $data;
        }, $translations);

        // Reset keys
        $translations = array_values($translations);

        // Write translations to the file
        $this->writeToFile($translations);

        return;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

    /**
     * @param $translations
     * @return mixed
     */
    private function writeToFile($translations)
    {
        $excel = app('excel');
        $filename = config('netcore.module-translate.translations_file');

        return $excel->create($filename, function ($excel) use ($translations) {

            $excel->setTitle('Translations');
            $excel->sheet('Translations', function ($sheet) use ($translations) {

                $languages = Language::all();

                $rows = [
                    [
                        'key',
                    ],
                ];

                foreach ($languages as $language) {
                    $rows[0][] = $language->iso_code;
                }

                // Now $rows would be something like ['key', 'lv', 'ru']
                $translations = array_map(function ($t) use ($languages) {

                    $item = [
                        $t['key'],
                    ];

                    foreach ($languages as $language) {
                        $item[] = $t[$language->iso_code];
                    }

                    return $item;
                }, $translations);

                $rows = array_merge($rows, ($translations));

                $sheet->fromArray($rows, null, 'A1', false, false);

                $sheet->row(1, function ($row) {
                    $row->setFontWeight('bold');
                });

            });
        })->store('xlsx', resource_path('seed_translations'));
    }

    /**
     * @param $group
     * @param $key
     * @param $value
     * @return array
     */
    private function makeRows($group, $key, $value)
    {
        $rows = [];

        if (is_array($value)) {
            foreach ($value as $subkey => $subvalue) {
                $rows[] = [
                    'key'   => $group . '.' . $key . '.' . $subkey,
                    'value' => $subvalue,
                ];
            }
        } else {
            $rows[] = [
                'key'   => $group . '.' . $key,
                'value' => $value,
            ];
        }

        return $rows;
    }
}

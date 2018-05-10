<?php

namespace Modules\Translate\Console;

use Illuminate\Console\Command;
use Netcore\Translator\Helpers\TransHelper;
use Netcore\Translator\Models\Translation;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
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

        $finder = new Finder();
        $finder->in($paths)->name('*.php')->files();

        foreach ($finder as $file) {
            if (preg_match_all('/(lg)\(((?:[^\(\)]++|(?R))*)\)/', $file->getContents(), $matches)) {
                foreach ($matches[2] as $i => $match) {
                    $value = explode(',', $match);
                    $key = $value[0];

                    if (count($value) > 2) {
                        $val = isset($value[3]) ? $value[3] : $value[2];
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

        $translations = array_map(function ($translation) {
            $data = [
                'key' => $translation['key']
            ];

            foreach (TransHelper::getAllLanguages() as $language) {
                $data[$language->iso_code] = $translation['value'];
            }

            return $data;
        }, $translations);

        // Add the translations to the database, if not existing.
        $translation = new Translation();
        $translation->import()->process($translations);

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
}

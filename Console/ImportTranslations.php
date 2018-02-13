<?php

namespace Modules\Translate\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Netcore\Translator\Helpers\TransHelper;
use Netcore\Translator\Models\Translation;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ImportTranslations extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'translations:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports translations from file';

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
     */
    public function handle()
    {
        DB::table('translations')->delete();

        if(config('netcore.module-translate.import_default_translations', true)) {
            $this->importFromLangFiles();
        }

        $fileName = config('netcore.module-translate.translations_file');
        $excelLocation = resource_path('seed_translations/' . $fileName . '.xlsx');
        if (file_exists($excelLocation)) {
            try {
                $excel = app('excel');
                $all_data = $excel->load($excelLocation)
                    ->get()
                    ->toArray();

                $this->import($all_data);

            } catch (\Exception $e) {
                echo "\n\n Could not import translations from excel file. Perhaps format is wrong. \n\n";
            }
        }

        cache()->flush();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [

        ];
    }

    /**
     * @param $translations
     */
    private function import($translations)
    {
        $translation = new Translation();
        $translation->import()->process($translations);
    }

    /**
     *
     */
    private function importFromLangFiles()
    {
        $file = resource_path('lang');
        $files = \File::allFiles($file);

        $translations = [];

        if(file_exists($file)) {
            foreach ($files as $file) {
                $locale = basename($file->getPath());
                $fullPath = $file->getPathname();
                $group = str_replace('.php', '', $file->getFilename());
                foreach (\File::getRequire($fullPath) as $key => $translation) {
                    foreach($this->makeRows($locale, $group, $key, $translation) as $row) {
                        if($row['key'] == 'custom.attribute-name') {
                            continue;
                        }
                        $translations[] = $row;
                    }
                }
            }
        }

        Translation::insert($translations);
    }

    /**
     * @param String $locale
     * @param String $group
     * @param String $key
     * @param        $value
     * @return array
     */
    private function makeRows(String $locale, String $group, String $key, $value)
    {
        $rows = [];

        if (is_array($value)) {
            foreach ($value as $subkey => $subvalue) {
                // domain, group, key, value
                $rows[] = [
                    'locale' => $locale,
                    'group'  => $group,
                    'key'    => $key . '.' . $subkey,
                    'value'  => $subvalue,
                ];
            }
        } else {
            // domain, group, key, value
            $rows[] = [
                'locale' => $locale,
                'group'  => $group,
                'key'    => $key,
                'value'  => $value,
            ];
        }

        return $rows;
    }
}

<?php

namespace Modules\Translate\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
        $fileName = config('netcore.module-translate.translations_file');
        $excelLocation = resource_path('seed_translations/' . $fileName . '.xlsx');
        if (file_exists($excelLocation)) {
            try {
                $excel = app('excel');
                $all_data = $excel->load($excelLocation)
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {
                echo "\n\n Could not import translations from excel file. Perhaps format is wrong. \n\n";
            }

            $translation = new Translation();
            $translation->import()->process($all_data);
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
}

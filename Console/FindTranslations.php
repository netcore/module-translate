<?php

namespace Modules\Translate\Console;

use File;
use PhpParser\Lexer;
use PhpParser\ParserFactory;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Netcore\Translator\Helpers\TransHelper;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

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
    protected $description = 'Find translations in project and write to the excel sheet.';

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
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(): void
    {
        $paths = $this->getPaths();
        $translations = [];

        // Default laravel translations.
        $files = File::allFiles(
            resource_path('lang/en')
        );

        foreach ($files as $file) {
            $fullPath = $file->getPathname();
            $group = str_replace('.php', '', $file->getFilename());

            foreach (File::getRequire($fullPath) as $key => $translation) {
                foreach ($this->makeRows($group, $key, $translation) as $row) {
                    if ($row['key'] === 'validation.custom.attribute-name') {
                        continue;
                    }

                    $translations[$row['key']] = $row['value'];
                }
            }
        }

        // Find translations in files
        $finder = new Finder();
        $finder
            ->in($paths)
            ->name('*.php')
            ->notName('FindTranslations.php')
            ->files();

        // Init parser.
        $parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7, new Lexer());

        foreach ($finder as $file) {
            if (preg_match_all('/lg(\(((?:[^()]*|(?-2))*)\))/', $file->getContents(), $matches)) {
                foreach ($matches[0] as $caller) {
                    $functionInfo = $parser->parse('<?php ' . $caller . ' ?>');
                    $functionInfo = $functionInfo[0];

                    $i = 0;
                    $key = '';
                    $value = '';

                    foreach ($functionInfo->expr->args as $argument) {
                        if ($argument->value->getType() === 'Scalar_String') {
                            if (!$i) {
                                $key = $argument->value->value;
                            } else {
                                $value = preg_replace('!\s+!', ' ', $argument->value->value);
                            }
                        }

                        $i++;
                    }

                    $translations[$key] = $value;
                }
            }
        }

        // Map translations.
        $mappedTranslations = [];

        foreach ($translations as $key => $text) {
            $data = compact('key');

            foreach (TransHelper::getAllLanguages() as $language) {
                $data[$language->iso_code] = $text;
            }

            $mappedTranslations[] = $data;
        }

        // Write translations to the file.
        $this->writeToFile($mappedTranslations);

        // Import to DB.
        if ($this->option('import')) {
            $this->call('translations:import');
        }
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
        return [
            ['import']
        ];
    }

    /**
     * Write translations to the file.
     *
     * @param array $translations
     * @return void
     */
    protected function writeToFile(array $translations): void
    {
        $excel = app('excel');
        $filename = config('netcore.module-translate.translations_file');

        $excel
            ->create($filename, function (LaravelExcelWriter $writer) use ($translations) {
                $writer->setTitle('Translations');

                $writer->sheet('Translations', function (LaravelExcelWorksheet $sheet) use ($translations) {
                    $sheet->fromArray($translations, '', 'A1');

                    $sheet->row(1, function ($row) {
                        $row->setFontWeight('bold');
                    });
                });
            })
            ->store('xlsx', resource_path('seed_translations'));
    }

    /**
     * Create translations from array.
     *
     * @param $group
     * @param $key
     * @param $value
     * @return array
     */
    protected function makeRows($group, $key, $value): array
    {
        $rows = [];

        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                $rows[] = [
                    'key'   => $group . '.' . $key . '.' . $subKey,
                    'value' => $subValue,
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

    /**
     * Get paths in which to search for translations.
     *
     * @return array
     */
    protected function getPaths(): array
    {
        $paths = [
            base_path('app'),
            base_path('modules'),
            base_path('resources'),
            base_path('routes'),
        ];

        foreach ($paths as $i => $path) {
            if (!File::isDirectory($path)) {
                unset($paths[$i]);
            }
        }

        return $paths;
    }
}

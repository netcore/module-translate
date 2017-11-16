<?php

namespace Modules\Translate\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Models\Menu;
use Netcore\Translator\Helpers\TransHelper;

class MenusTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $config = config('netcore.module-translate.menu');

        $menuItems = [
            'leftAdminMenu' => [
                [
                    'name'            => 'Translate',
                    'icon'            => 'fa fa-globe',
                    'type'            => 'url',
                    'value'           => '#',
                    'module'          => 'Translate',
                    'is_active'       => 1,
                    'active_resolver' => 'admin.translations.*,admin.languages.*',
                    'parameters'      => json_encode([]),
                    'children'        => [
                        [
                            'name'            => 'Translations',
                            'type'            => 'route',
                            'value'           => 'admin.translations.index',
                            'module'          => '',
                            'is_active'       => 1,
                            'active_resolver' => 'admin.translations.*',
                            'parameters'      => json_encode([])
                        ],
                        [
                            'name'            => 'Languages',
                            'type'            => 'route',
                            'value'           => 'admin.languages.index',
                            'module'          => '',
                            'is_active'       => 1,
                            'active_resolver' => 'admin.languages.*',
                            'parameters'      => json_encode([])
                        ],
                    ]
                ],
            ]
        ];

        foreach ($menuItems as $name => $items) {
            $menu = Menu::firstOrCreate([
                'name' => $name
            ]);

            foreach ($items as $item) {
                $row = $menu->items()->firstOrCreate(array_except($item, ['name', 'value', 'parameters', 'children']));

                $translations = [];
                foreach (TransHelper::getAllLanguages() as $language) {
                    $translations[$language->iso_code] = [
                        'name'       => $item['name'],
                        'value'      => $item['value'],
                        'parameters' => $item['parameters']
                    ];
                }
                $row->updateTranslations($translations);

                foreach ($item['children'] as $child) {
                    $child['menu_id'] = $menu->id;

                    if ((!$config['translations'] && $child['name'] == 'Translations') || (!$config['languages'] && $child['name'] == 'Languages')) {
                        continue;
                    }

                    $c = $row->children()->firstOrCreate(array_except($child, ['name', 'value', 'parameters']));
                    $translations = [];
                    foreach (TransHelper::getAllLanguages() as $language) {
                        $translations[$language->iso_code] = [
                            'name'       => $child['name'],
                            'value'      => $child['value'],
                            'parameters' => $child['parameters']
                        ];
                    }
                    $c->updateTranslations($translations);
                }
            }
        }
    }
}

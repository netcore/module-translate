<?php

namespace Modules\Translate\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Models\Menu;
use Modules\Admin\Models\MenuItem;

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
                    'name'       => 'Translate',
                    'icon'       => '',
                    'type'       => 'url',
                    'value'      => '#',
                    'module'     => '',
                    'is_active'  => 1,
                    'parameters' => json_encode([]),
                    'children'   => [
                        [
                            'name'       => 'Translations',
                            'type'       => 'url',
                            'value'      => '/admin/translations',
                            'module'     => '',
                            'is_active'  => 1,
                            'parameters' => json_encode([])
                        ],
                        [
                            'name'       => 'Languages',
                            'type'       => 'url',
                            'value'      => '/admin/languages',
                            'module'     => '',
                            'is_active'  => 1,
                            'parameters' => json_encode([])
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
                $item['menu_id'] = $menu->id;
                $item['parent_id'] = null;
                $parentItem = MenuItem::firstOrCreate(array_except($item, 'children'));

                if(isset($item['children'])) {
                    foreach ($item['children'] as $child) {
                        $child['parent_id'] = $parentItem->id;
                        $child['menu_id'] = $menu->id;

                        if((!$config['translations'] && $child['name'] == 'Translations') || (!$config['languages'] && $child['name'] == 'Languages')) {
                            continue;
                        }
                        MenuItem::firstOrCreate($child);
                    }
                }

            }
        }
    }
}

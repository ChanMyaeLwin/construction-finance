<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectTypeSeeder extends Seeder {
    public function run(): void {
        $types = [
            ['name' => 'Residential', 'description' => null],
            ['name' => 'Commercial',  'description' => null],
            ['name' => 'Industrial',  'description' => null],
            ['name' => 'Infrastructure','description' => null],
        ];
        foreach ($types as $t) {
            DB::table('project_types')->updateOrInsert(['name'=>$t['name']], $t + ['created_at'=>now(),'updated_at'=>now()]);
        }
    }
}

<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class Drop_down_values extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('dynamic_dropdown_values')->insert([
            [
                'client_id' => 48,
                'type_id'=>5,
                'display_text' => 'personal reason',
                'value'=>'personal reason',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'type_id'=>5,
                'display_text' => 'reallocation',
                'value'=>'reallocation',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'type_id'=>5,
                'display_text' => 'Retirment',
                'value' => 'Retirment',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'type_id'=>5,
                'display_text' => 'Another Position',
                'value' => 'Another Position',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'type_id'=>5,
                'display_text' => 'Batter opperunity',
                'value' => 'Batter opperunity',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'type_id'=>6,
                'display_text' => 'company policy',
                'value' => 'company policy',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'type_id'=>6,
                'display_text' => 'One Month',
                'value' => 'One Month',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'type_id'=>6,
                'display_text' => 'one week',
                'value' => 'one week',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            
        ]);
        
    }
}

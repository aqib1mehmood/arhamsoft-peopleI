<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class add_interview_question extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('interview_question')->insert([
            [
                'client_id' => 48,
                'statment'=>'this is starting test question',
                'type_id'=>3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is starting test question 123',
                'type_id'=>3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is starting test question abc',
                'type_id'=>3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is starting test question xyz',
                'type_id'=>3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is starting test question who',
                'type_id'=>3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is starting test question hbo',
                'type_id'=>3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is starting test acdemic question',
                'type_id'=>4,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this starting is starting test question hbo',
                'type_id'=>4,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is hello world starting test question hbo',
                'type_id'=>4,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is starting test question positive',
                'type_id'=>4,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'statment'=>'this is starting test question working good',
                'type_id'=>4,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }
}

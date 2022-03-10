<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class add_question_types_record extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('interview_question_type')->insert([
            [
                'client_id' => 48,
                'title' => 'Start Question',
                'description'=>'this is starting test question',
                'type'=>1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'client_id' => 48,
                'title' => 'Start Question',
                'description'=>'this is starting acdemic question',
                'type'=>2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]

            
        ]);
    }
}

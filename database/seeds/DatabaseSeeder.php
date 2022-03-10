<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
     //   $this->call(add_drop_dwon_types::class);
       // $this->call(Drop_down_values::class);
          // $this->call(add_question_types_record::class);
           $this->call(add_interview_question::class);
        
    }
}

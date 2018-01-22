<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {   
        //clear table
        User::truncate();

        User::create([
            'name' => 'username',
            'email' => 'user@email.com',
            'password' => bcrypt('userpass'),
        ]);
    }
}
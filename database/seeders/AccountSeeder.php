<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('account_types')->insert([
            [
                'name' => 'asset',
                'code' => '10'
            ],
            [
                'name' => 'liability',
                'code' => '20'
            ],
            [
                'name' => 'capital',
                'code' => '30'
            ],
            [
                'name' => 'Income',
                'code' => '40'
            ],
            [
                'name' => 'expense',
                'code' => '50'
            ],
        ]);
    }
}

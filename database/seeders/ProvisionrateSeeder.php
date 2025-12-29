<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvisionrateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('provision_rates')->insert([
            [
                'name' => 'performing',
                'days' => '0',
                'rate' => '1.00',
                'notes' => null
            ],
            [
                'name' => 'pass & watch',
                'days' => '31',
                'rate' => '5.00',
                'notes' => null
            ],
            [
                'name' => 'substandard',
                'days' => '61',
                'rate' => '10.00',
                'notes' => null
            ],
            [
                'name' => 'doubtful',
                'days' => '91',
                'rate' => '50.00',
                'notes' => null
            ],
            [
                'name' => 'lost',
                'days' => '181',
                'rate' => '100.00',
                'notes' => null
            ]
        ]);
    }
}

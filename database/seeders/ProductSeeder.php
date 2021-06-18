<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::factory()
            ->count(50)
            ->create();


//        Product::create([
//            'user_id' => 1000000,
//            'name' => 'Cyber',
//            'slug' => 'cyber-1',
//            'quantity' => 50,
//            'remaining' => 50,
//            'price' => 400,
//            'description' => 'sdafasdfasdfsdafasdf',
//            'thumbnail' => 'temp.jpg',
//            'status' => 'Active',
//        ]);
//
//        DB::table('category_product')->insert(
//            ['product_id' => 2000001, 'category_id' => 1]
//        );
    }
}

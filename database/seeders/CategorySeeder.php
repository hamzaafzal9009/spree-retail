<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::create(['name' => 'New Arrivals', 'main' => 'Featured', 'slug' => 'new-arrivals']); //1
        Category::create(['name' => 'Latest', 'main' => 'Featured', 'slug' => 'latest']); //2
        Category::create(['name' => 'Fashion', 'main' => 'Main', 'slug' => 'fashion']); //3
        Category::create(['name' => 'Health & Beauty', 'main' => 'Main', 'slug' => 'health']); //4
        Category::create(['name' => 'Electronics', 'main' => 'Main', 'slug' => 'electronics']); //5
        Category::create(['name' => 'Groceries', 'main' => 'Main', 'slug' => 'groceries']); //6

        Category::create(['name' => 'Men', 'main' => 'Main', 'slug' => 'men']); //7
        Category::create(['name' => 'Women', 'main' => 'Main', 'slug' => 'women']); //8

        foreach (config('enums.men') as $item) {
            Category::create(['name' => $item, 'main' => 'Sub', 'slug' => $item, 'parent_id' => 7]);
        }
        foreach (config('enums.women') as $item) {
            if ($item != "Shoes") {
                Category::create(['name' => $item, 'slug' => "$item", 'parent_id' => 8]);
            } else {
                Category::create(['name' => $item, 'slug' => "women-shoes", 'parent_id' => 8]);
            }
        }
        foreach (config('enums.groceries') as $item) {
            Category::create(['name' => $item, 'slug' => $item, 'parent_id' => 6]);
        }
        foreach (config('enums.health') as $item) {
            Category::create(['name' => $item, 'slug' => $item, 'parent_id' => 4]);
        }

    }
}

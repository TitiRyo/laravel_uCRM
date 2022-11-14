<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\Purchase;
use \App\Models\Item;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ItemSeeder::class,
        ]);
        \App\Models\Customer::factory(1000)->create();

        //purchaseが登録されたときに一緒に中間テーブルの内容も登録できるようにする。
        //eachは一件ずつ処理をするということ。中はcallbackで引数はテーブル
        //use文で関数の外側の変数も中で使用することができる
        //attachで中間テーブルに登録することができる　purchase_idは自動で登録できるのでその他を記述していく

        $items = Item::all();
        Purchase::factory(100)->create()
        ->each(function (Purchase $purchase) use ($items) {
            $purchase->items()->attach($items->random(rand(1, 3))->pluck('id')->toArray(), ['quantity' => rand(1, 5)]);
        });
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

    }
}

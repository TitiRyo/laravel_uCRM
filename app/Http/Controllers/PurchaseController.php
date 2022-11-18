<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Item;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Order;


class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        //idごとに集計
        //先ほどscopeのsubtotalの中で書いたクエリをOrderだけで実現している
        $orders = Order::groupBy('id')
        //selectRawを使用して中は生のSQL文を記述する
        ->selectRaw('id , sum(subtotal) as total, customer_name, status, created_at')
        ->paginate(50);

        return Inertia::render('Purchases/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //customerの情報を取得する
        // $customers = Customer::select('id', 'name', 'kana')->get();
        //itemから情報を取得する。条件でsellingがtrueのものだけを取得するように条件をしている
        $items = Item::select('id', 'name', 'price')->where('is_selling', true)->get();

        return Inertia::render('Purchases/Create', [
            // 'customers' => $customers,
            'items' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePurchaseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseRequest $request)
    {
        DB::beginTransaction();
        try {
        $purchase = Purchase::create([
            'customer_id' => $request->customer_id,
            'status' => $request->status,
        ]);

        //複数の商品を1つずつ設定することができる
        foreach($request->items as $item) {
            //attachで中間テーブルに挿入する、第一引数で紐ずくデータベスのId（purchaseが追加されたら付随してってこと）,第二引数は連想配列で書くことができる
            $purchase->items()->attach($purchase->id, [
                'item_id' => $item['id'],
                'quantity' => $item['quantity']
            ]);
        } 
        DB::commit();
        return to_route('dashboard');
        
        } catch(\Exception $e) {
            DB::rollBack();
        }

        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {
        //小計 whereで絞ることで一件だけ取得することができる
        $items = Order::where('id', $purchase->id)->get();
        //合計
        $order = Order::groupBy('id')
        ->where('id', $purchase->id)
        //selectRawを使用して中は生のSQL文を記述する
        ->selectRaw('id , sum(subtotal) as total, customer_name, status, created_at')
        ->get();

        // dd($items, $order);

        return Inertia::render('Purchases/Show', [
            'items' => $items,
            'order' => $order,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        //数量は中間テーブルなので、まずは中間テーブルの情報をとる
        //購買IDから$purchase->itemsとすることで、以前リレーションで設定したModelのitems()を使い紐付いた情報を撮ることができる
        //そして$item->pivot->quantityとすることで数量を取得することができる
        $purchase = Purchase::find($purchase->id);//vue側から渡ってくるIDを指定して一件だけ購買IDの情報を取得することができる

        //すべての商品情報
        $allItems = Item::select('id', 'name', 'price')->get();

        //この二つのIDを組み合わせて新しい配列の作成する
        $items = [];

        //すべてのitemを一つずつ確認するのでforeachで回す
        foreach($allItems as $allItem) {
            //初期値
            $quantity = 0;
            //中間テーブルの情報を一つずつチェック
            foreach($purchase->items as $item) {
                if($allItem->id === $item->id) {
                    $quantity = $item->pivot->quantity;
                }
            }
            array_push($items, [
                'id' => $allItem->id,
                'name' => $allItem->name,
                'price' => $allItem->price,
                'quantity' => $quantity,
            ]);
        }
        // dd($items);
        $order = Order::groupBy('id')
        ->where('id', $purchase->id)
        //selectRawを使用して中は生のSQL文を記述する
        ->selectRaw('id , customer_id, customer_name, status, created_at')
        ->get();

        return Inertia::render('Purchases/Edit', [
            'items' => $items,
            'order' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePurchaseRequest  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePurchaseRequest $request, Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            //中間テーブルの情報を更新するときはsync()が便利
            $purchase->status = $request->status;
            $purchase->save();
            $items = [];
            foreach($request->items as $item) {
            $items = $items + [
            // item_id => [ 中間テーブルの列名 => 値 ] 
            $item['id'] => [ 'quantity' => $item['quantity']]
            ]; }
            $purchase->items()->sync($items);
            DB::commit();
            return to_route('dashboard');
        } catch(\Exception $e) {
            DB::rollBack();
        }
    }   

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}

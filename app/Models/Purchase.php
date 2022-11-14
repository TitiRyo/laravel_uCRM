<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Item;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
    ];

    //Purchase側からも見るかもしれないので.
    //こちらは1人の顧客だけを取得するので単数系で記述
    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    //PurchaseからItemを取得したいので
    //withPivotで中間テーブルにしかない情報を取得する時に記入する
    public function items() {
        return $this->belongsToMany(Item::class)->withPivot('quantity');
    }
}
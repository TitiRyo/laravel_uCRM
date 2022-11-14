<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    //$fillableを指定してまとめて登録できるようにする
    protected $fillable = [
        'name',
        'kana',
        'tel',
        'email',
        'postcode',
        'address',
        'birthday',
        'gender',
        'memo',
    ];

    //コントローラーなどからcustomerモデルを使うときにSearchCustomersというメソッドとして使うことができる
    public function scopeSearchCustomers($query, $input = null) {
        if(!empty($input)) {
            if(Customer::where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%')->exists()) {
                return $query->where('kana', 'like', $input . '%')->orWhere('tel', 'like', $input . '%');
            }
        }
    }

    //Customer側
    //購入者1人に対して購入履歴が複数見れるため１対多の関係になるので、そのためにリレーションを行う
    //複数の購買情報を見れるように複数形にしておく
    public function purchases() {
        // HasManyの中にmodelの情報を書いてあげる
        return $this->hasMany(Purchase::class);
    }

}

<script setup>
import { getToday } from '@/common';
import { onMounted, reactive, ref, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    customers: Array,
    items: Array,
})

// Purchase
const form = reactive({
    date: null,
    customer_id: null,
    status: true,
    //itemは複数選ばれる可能性があるので配列で渡す
    items: [],
});

const quantity = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

// item_purchase
const itemList = ref([]);


onMounted(() => {
    form.date = getToday();
    props.items.forEach(item => {
        //このようにすることで渡ってきたitemsからリアクティブな新しい配列ができる
        itemList.value.push({
            id: item.id,
            name: item.name,
            price: item.price,
            quantity: 0,
        })
    })
})

const totalPrice = computed(() => {
    //初期値
    let total = 0;
    itemList.value.forEach(item => {
        total += item.price * item.quantity;
    })
    return total
})



const storePurchase = () => {
    itemList.value.forEach(item => {
        if(item.quantity > 0) {
            form.items.push({
                id: item.id,
                quantity: item.quantity
            })
        }
    })
    Inertia.post(route('purchases.store'), form);
}
</script>
<template>
<form @submit.prevent="storePurchase">
 日付<br>
 <input type="date" name="date" v-model="form.date"><br>

 会員名<br>
 <!-- 商品購入の画面から情報を登録するとPurchaseテーブルにcustomer_idを登録したいということでformにcustomer_idの項目を足してselectタグのv-modelにformのcustomer_idを入れている -->
 <!-- こうするとselectboxにバーっと表示されて1人の顧客を選ぶとそのcustomer_idをformの中に情報が入り登録することができる -->
 <select name="customer" v-model="form.customer_id">
    <option v-for="customer in customers" :value="customer.id" :key="customer.id">
        {{customer.id}} : {{customer.name}}
    </option>
 </select>
 <br><br>

 商品・サービス<br> 
 <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>商品</th>
            <th>金額</th>
            <th>数量</th>
            <th>小計</th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="item in itemList" > 
            <td>{{ item.id }}</td>
            <td>{{ item.name }}</td> 
            <td>{{ item.price }}</td> 
            <td>
                <select name="quantity" v-model="item.quantity"> 
                    <option v-for="q in quantity" :value="q">{{ q }}</option>
                </select>
            </td>
            <td>
            {{ item.price * item.quantity }}
            </td> 
        </tr>
    </tbody> 
</table>
<br>
合計: {{ totalPrice }}円<br>
<button>登録</button>
</form>
</template>
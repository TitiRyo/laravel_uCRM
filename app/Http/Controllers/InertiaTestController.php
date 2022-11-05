<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\inertia;
use App\Models\InertiaTest;

class InertiaTestController extends Controller
{
    //コントローラーからviewに渡すときはinertiarenderを使う必要があるためuseでInertiaを取得する
    public function index() {
        return Inertia::render('Inertia/index',[
            'blogs' => InertiaTest::all()
        ]);
    }

    public function create() {
        return Inertia::render('Inertia/create');
    }

    public function show($id) {
        return Inertia::render('Inertia/show', 
    [
        //値を渡したい時は連想配列で渡す
        'id' => $id,
        'blog' => InertiaTest::findOrFail($id)
    ]);
    }

    public function store(Request $request) {

        $request->validate([
            'title' => ['required', 'max:20'],
            'content' => ['required'],
        ]);

        $inertiaTest = new InertiaTest();
        $inertiaTest->title = $request->title;
        $inertiaTest->content = $request->content;
        $inertiaTest->save();



        return to_route('inertia.index')->with([
            'message' => '登録しました'
        ]);
    }

    public function delete($id) {
        $book = InertiaTest::findOrFail($id);
        $book->delete();
        return to_route('inertia.index')->with([
            'message' => '削除しました'
        ]);
    }
}

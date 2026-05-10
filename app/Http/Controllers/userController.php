<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class userController extends Controller
{
    public function index ()
    {
        $name='omar';
        $age='20';
        return view('test',compact('name','age'));
    }

    public function store(Request $request)
    {
        $validatedData=$request->validate([
        'name'=>'required|max:10',
        'email'=>'required|email',
        'password'=>'required|min:8',
        ]);
        User::create($validatedData);
        return back()->with('success','registering successfully');
        //dd(request()->all());
    }
}

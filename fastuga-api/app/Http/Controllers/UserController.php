<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::all());
    }

    public function store(StoreUserRequest $request)
    {
        /* --- Authorization --- */
        if ($request['type'] != 'C' && Auth()->user()->type() != "EM") { abort(403); }

        $new_user = DB::transaction(function () use ($request) : User {
            $user = User::create($request->validated());
            $password_hashed = Hash::make($request->input('password'));
            $user->password = $password_hashed;
            $user->save();
            return $user;
        });

        return new UserResource($new_user);
    }

    public function show($id)
    {
        return new UserResource(User::findOrFail($id));
    }

    public function update(StoreUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $fields = $request->validated();
        $user->fill($fields);
        $user->save();

        return new UserResource($user);
    }

    public function toogle($id) {
        $user = User::findOrFail($id);
        $user->blocked = $user->blocked == 1 ? 0 : 1;
        $user->save();
        return new UserResource($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // if ($user->customer) { $user->customer->forceDelete(); } --> NONSENSE?
        return $user->delete();
    }
}

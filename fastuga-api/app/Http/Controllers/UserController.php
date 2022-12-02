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
    public function index(Request $request)
    {
        $users = $request->has('type') ? User::where('type', $request->input('type'))->paginate(10) : User::paginate(10);
        return UserResource::collection($users);
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

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(StoreUserRequest $request, User $user)
    {
        $user->fill($request->validated());
        $user->save();
        return new UserResource($user);
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $user = User::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
            if ($user->customer) { $user->customer->delete(); }
            return $user->delete();
        });
    }

    /* --- Custom Routes --- */

    public function toogle($id) {
        /* --- Authorization --- */
        if (Auth()->user()->type() != "EM") { abort(403); }

        $user = User::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
        $user->blocked = $user->blocked == 1 ? 0 : 1;
        $user->save();
        return new UserResource($user);
    }
}

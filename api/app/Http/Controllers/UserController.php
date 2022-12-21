<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct()
    {
        //$this->middleware('can:viewAny')->only('viewAny');
        //$this->middleware('can:create')->only('create');
        //$this->middleware('can:update')->only('update');
        //$this->middleware('can:delete')->only('delete');
        //$this->middleware('can:show_me')->only('show_me');
        //$this->middleware('can:toogle')->only('toogle');
        //$this->middleware('can:new_password')->only('new_password');



        /*
        $this->middleware('auth.manager', ['except' => [
            'show',
            'store',
            'status',
            'update',
            'show_me',
            'new_password'

        ]]);
        /*
        $this->middleware('auth.chef', ['except' => [
            'update',
            'show_me',
            'new_password'
        ]]);
        */
    }
    public function index(Request $request)
    {
         /* --- Authorization --- */
         if (Auth()->guard('api')->user()->type != "EM") { abort(403); }
        $users = $request->type != 'all' ? User::where('type', $request->input('type'))->paginate(10) : User::paginate(10);
        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        /* --- Authorization --- */
        if ($request['type'] != 'C' && Auth()->guard('api')->user()->type != "EM") { abort(403); }

        $new_user = DB::transaction(function () use ($request) : User {
            $user = User::create($request->validated());
            $password_hashed = Hash::make($request->input('password'));
            $user->password = $password_hashed;

            if ($request->has('photo_url')) {

                /*

                // -> Check if a previous file exists and deletes it
                //This isn't working
                if(Storage::disk('public')->exists($user->photo_url)) {
                    Storage::delete($user->photo_url);
                }
                  */
                $folderPath = "public/fotos/";

                $image_parts = explode(";base64,", $user->photo_url);

                $image_type_aux = explode("image/", $image_parts[0]);

                $image_type = $image_type_aux[1];

                $image_base64 = base64_decode($image_parts[1]);

                dd($image_base64);

                $uniqid=uniqid();
                $id_user=$user->id;


                $file="{$id_user}_ {$uniqid}.{$image_type}";

                Storage::put($folderPath.$file, $image_base64);


                $user->photo_url = $file;

            }
            $user->save();
            return $user;
        });

        return new UserResource($new_user);
    }

    public function show(User $user)
    {
        /* --- Authorization --- */
        if ((!Auth()->guard('api')->user()->type == "EM") && (Auth()->guard('api')->user()->type !="EM" && Auth()->guard('api')->user()->id!=$user->id) ) { abort(403); }
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
          /* --- Authorization --- */
          if ((!Auth()->guard('api')->user()->type == "EM") && (Auth()->guard('api')->user()->type !="EM" && Auth()->guard('api')->user()->id!=$user->id) ) { abort(403); }
        $user->fill($request->validated());
        $user->blocked = $user->blocked==1 ? 0 : 1;

        if ($request->has('photo_url')) {



            // -> Check if a previous file exists and deletes it
            //This isn't working
            if(Storage::disk('public')->exists($user->photo_url)) {
                Storage::delete($user->photo_url);
            }


            $folderPath = "public/fotos/";

            $image_parts = explode(";base64,", $user->photo_url);

            $image_type_aux = explode("image/", $image_parts[0]);

            $image_type = $image_type_aux[1];

            $image_base64 = base64_decode($image_parts[1]);





            $uniqid=uniqid();
            $id_user=$user->id;


            $file="{$id_user}_ {$uniqid}.{$image_type}";

            // -> Stores the new photo

            Storage::put($folderPath.$file, $image_base64);


            $user->photo_url = $file;

        }
        $user->save();
        return new UserResource($user);
    }

    public function destroy($id) // -> Boolean Return
    {
         /* --- Authorization --- */
         if (Auth()->guard('api')->user()->type != "EM") { abort(403); }
        return DB::transaction(function () use ($id) {
            $user = User::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
            if ($user->customer) { $user->customer->delete(); }
            return $user->delete();
        });
    }

    /* --- Custom Routes --- */

    public function show_me(Request $request) { return new UserResource($request->user()); }

    public function toogle($id) {
        /* --- Authorization --- */
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }
        $user = User::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
        $user->blocked = $user->blocked == 1 ? 0 : 1;
        $user->save();
        return new UserResource($user);
    }

    public function new_password(UpdateUserPasswordRequest $request, User $user) {
        if (Auth()->guard('api')->user()->type == "EM") { abort(403); }

        $user = User::where(['id' => $user->id], ['deleted_at' => null])->firstOrFail();
        $password_hashed = Hash::make($request->input('password'));
        $user->password = $password_hashed;
        $user->save();
        return new UserResource($user);
    }
/*
    protected function resourceAbilityMap()
    {
        return array_merge(parent::resourceAbilityMap(), [
            'show_me' => 'show_me',
            'toogle' => 'toogle',
            'new_password' => 'new_password'
    
        ]);
    }
    protected function resourceMethodsWithoutModels()
    {
        return array_merge(parent::resourceMethodsWithoutModels(), [
            'show_me',
            'toogle',
            'new_password'
        ]);
    }
    */
}

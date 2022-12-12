<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
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
            $user->save();
            return $user;
        });

        return new UserResource($new_user);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->fill($request->validated());
        $user->blocked = $user->blocked == 1 ? 0 : 1;

        if ($request->has('photo_url')) {


            // -> Check if a previous file exists and deletes it
            if(Storage::disk('public')->exists($user->photo_url)) {
                Storage::delete($user->photo_url);
            }
/*
            $image = $user->photo_url;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            dd(base64_decode($image));

            $imageName = str_random(10) . '.png';
            

  
          Storage::disk('public/fotos')->put($imageName, base64_decode($image));
          */ 
          
            //$folderPath = "fotos/";
/*
            $image_parts = explode(";base64,", $user->photo_url);
    
            $image_type_aux = explode("image/", $image_parts[0]);
    
            $image_type = $image_type_aux[1];
    
            $image_base64 = base64_decode($image_parts[1]);
            */
            
            //$image_base64 = base64_decode($user->photo_url);

            dd($image_base64);
            /*
            $uniqid=uniqid();
            $id_user=$user->id;
            //$photo_id = $photo->hashName();

            $file="{$id_user}_ {$uniqid}.{$image_type}";
            
            // -> Stores the new photo
            //$photo = $request->file('photo_url');
            //$photo_id = $photo->hashName();
            Storage::putFile('public/fotos', $file)->put($imageName, base64_decode($image));
            */
            $user->photo_url = $file;
            
        }
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
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }

        $user = User::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
        $user->blocked = $user->blocked == 1 ? 0 : 1;
        $user->save();
        return new UserResource($user);
    }

    public function show_me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function new_password(Request $request, User $user) {

        $user = User::where(['id' => $user->id], ['deleted_at' => null])->firstOrFail();
        $password_hashed = Hash::make($request->input('password'));
        $user->password = $password_hashed;
        $user->save();
        return new UserResource($user);
    }
}

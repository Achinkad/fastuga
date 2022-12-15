<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\StoreUserRequest;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\UserController;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\StoreCustomerRequest;


class CustomerController extends Controller
{
    public function index()
    {
        return CustomerResource::collection(Customer::all());
    }

    public function store(StoreCustomerRequest $customer_request, StoreUserRequest $user_request)
    {
        /* --- DB Transaction -> Create User + Customer --- */
        $new_customer = DB::transaction(function () use ($customer_request, $user_request) : Customer {
            // -> Creates User
            $create_user = (new UserController)->store($user_request);

            // -> Creates Customer
            $customer_fields = $customer_request->validated();
            $customer = new Customer;
            $customer->fill($customer_fields);
            $customer->user_id = trim($create_user->id);
/*
            // -> Stores Customer Photo
            if ($customer_request->has('photo_url')) {
                $photo = $customer_request->file('photo_url');
                $photo_id = $customer->id . '_' . $photo->hashName();
                Storage::putFileAs('public/fotos', $photo, $photo_id);
                $customer->photo_url = $photo_id;
            }
*/
            $customer->save();
            return $customer;
        });

        return new CustomerResource($new_customer);
    }

    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }

    public function update(StoreCustomerRequest $customer_request, UpdateUserRequest $user_request, Customer $customer)
    {
        $updated_customer = DB::Transaction(function () use ($customer_request, $user_request, $customer) : Customer {
            // -> Updates User
            $updated_user = (new UserController)->update($user_request, $customer->user);

            // -> Updates Customer
            $customer->fill($customer_request->validated());
            
            //dd($customer);
            if ($customer_request->has('photo_url')) {
                /*
                if(Storage::disk('public')->exists($customer->photo_url)) {
                    Storage::delete($customer->photo_url);
                }
                */
                // -> Stores the new photo
                $folderPath = "public/fotos/";

                $image_parts = explode(";base64,", $customer->photo_url);
        
                $image_type_aux = explode("image/", $image_parts[0]);
        
                $image_type = $image_type_aux[1];
        
                $image_base64 = base64_decode($image_parts[1]);
                
                
    
              
                
                $uniqid=uniqid();
                $id_user=$customer->user_id;
             
    
                $file="{$id_user}_ {$uniqid}.{$image_type}";
                
                // -> Stores the new photo
    
                Storage::put($folderPath.$file, $image_base64);
    
    
                $customer->photo_url = $file;
            }

            $customer->save();
            return $customer;
        });
        return new CustomerResource($updated_customer);
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $customer = Customer::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
            $customer->user->delete();
            return $customer->delete();
        });
    }

    public function showByUser(User $user)
    {
        
      
        return new CustomerResource($user->customer);
            
    }
}
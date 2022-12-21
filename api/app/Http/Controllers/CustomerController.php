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
    public function __construct()
    {

    }
    public function index()
    {
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }
        return CustomerResource::collection(Customer::all());
    }

    public function store(StoreCustomerRequest $customer_request, StoreUserRequest $user_request)
    {
        if (Auth()->guard('api')->user()->type != "C") { abort(403); }

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
        if ((!Auth()->guard('api')->user()->type == "EM") && (Auth()->guard('api')->user()->type !="EM" && Auth()->guard('api')->user()->id!=$customer->id) ) { abort(403); }

        return new CustomerResource($customer);
    }

    public function update(StoreCustomerRequest $customer_request, UpdateUserRequest $user_request, Customer $customer)
    {
        if (Auth()->guard('api')->user()->type != "C") { abort(403); }

        $updated_customer = DB::Transaction(function () use ($customer_request, $user_request, $customer) : Customer {
            // -> Updates User
            $updated_user = (new UserController)->update($user_request, $customer->user);
            if ($user_request->has('photo_url')) {

                if(Storage::disk('public')->exists($customer->photo_url)) {
                    Storage::delete($customer->photo_url);
                }

                // -> Stores the new photo
                $folderPath = "public/fotos/";

                $image_parts = explode(";base64,", $updated_user->photo_url);

                $image_type_aux = explode("image/", $image_parts[0]);

                $image_type = $image_type_aux[1];

                $image_base64 = base64_decode($image_parts[1]);





                $uniqid=uniqid();
                $id_user=$updated_user->id;


                $file="{$id_user}_ {$uniqid}.{$image_type}";

                // -> Stores the new photo

                Storage::put($folderPath.$file, $image_base64);


                $updated_user->photo_url = $file;
            }

            // -> Updates Customer
            $customer->fill($customer_request->validated());

            //dd($customer);


            $customer->save();
            return $customer;
        });
        return new CustomerResource($updated_customer);
    }

    public function destroy($id) // -> Boolean Return
    {
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }

        return DB::transaction(function () use ($id) {
            $customer = Customer::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
            $customer->user->delete();
            return $customer->delete();
        });
    }

    public function get_number_customers_created_this_month(){

        if(auth()->guard('api')->user() && auth()->guard('api')->user()->type == "EM"){
            $number_costumers=Customer::count();
            $customers_created_last_month=Customer::whereYear('created_at','=',date('Y'))->whereMonth('created_at','=',date('m')-1)->count();
            $customers_created_this_month=Customer::whereYear('created_at','=',date('Y'))->whereMonth('created_at','=',date('m'))->count();
            $percent_difference=0;
            if($customers_created_last_month!=0){
                $percent_difference=($customers_created_this_month-$customers_created_last_month)/$customers_created_last_month*100;
            }


            return array($number_costumers,$percent_difference);

        }
    }

    public function show_by_user(User $user){ return new CustomerResource($user->customer); }

}

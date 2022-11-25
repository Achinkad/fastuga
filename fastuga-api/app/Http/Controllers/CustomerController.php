<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

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

            if ($customer_request->has('photo_url')) {
                
            }

            $customer->save();

            return $customer;
        });

        return new CustomerResource($new_customer);
    }

    public function show($id)
    {
        return new CustomerResource(Customer::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $customer = Customer::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
            $customer->user->delete();
            return $customer->delete();
        });
    }
}

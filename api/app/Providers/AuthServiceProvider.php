<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\Order;
use App\Policies\OrderPolicy;

use App\Models\Product;
use App\Policies\ProductPolicy;

use App\Models\User;
use App\Policies\UserPolicy;

use App\Models\OrderItem;
use App\Policies\OrderItemPolicy;

use App\Models\Customer;
use App\Policies\CustomerPolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
            'App\Models\Model' => 'App\Policies\ModelPolicy',
            Order::class => OrderPolicy::class,
            Product::class => ProductPolicy::class,
            User::class => UserPolicy::class,
            OrderItem::class => OrderItemPolicy::class,
            Customer::class => CustomerPolicy::class,

        //'App\Models\Order' => 'App\Policies\OrderPolicy',

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('viewAll-order', function (User $user) {
            if($user->type=="EM"){
                return true;
            }
        });
        Gate::define('get_orders_user-order',function (User $user)
        {
            
            if($user->type=="ED" || $user->type=="C" ){
                return true;
            }
        });
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductResource;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:viewAny')->only('viewAny');
        $this->middleware('can:create')->only('create');
        $this->middleware('can:update')->only('update');
        $this->middleware('can:delete')->only('delete');
        /*
        $this->middleware('auth.manager', ['except' => [
            'index',
            'show',
            'get_best_selling_product'
        ]]);
        /*
        $this->middleware('auth.chef', ['except' => [
            'index',
            'show',
            'get_best_selling_product'
        ]]);
        */
    }

    public function index(Request $request)
    {
        $products = $request->type != 'all' ? Product::where('type', $request->input('type'))->paginate(10) : Product::paginate(10);
        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $product = new Product;
        $product->fill($request->validated());

        if ($request->has("photo_url")) {
            $image_id = Str::random(15) . "." . explode('/', explode(';', $request->input('photo_url'))[0])[1];
            Storage::put("public/products/" . $image_id, base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $product->photo_url)));
            $product->photo_url = $image_id;
        } else {
            $product->photo_url = "product-none.png";
        }

        $product->save();
        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        $product->fill($request->validated());

        if ($request->has('photo_url')) {
            // -> Check if a previous file exists and deletes it
            if(Storage::disk('public')->exists($product->photo_url)) {
                Storage::delete($product->photo_url);
            }

            $image_id = Str::random(15) . "." . explode('/', explode(';', $request->input('photo_url'))[0])[1];
            Storage::put("public/products/" . $image_id, base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $product->photo_url)));
            $product->photo_url = $image_id;
        } else {
            $product->photo_url = "product-none.png";
        }

        $product->save();
        return new ProductResource($product);
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $product = Product::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
            if ($product->order_item) { $product->order_item->detach(); }
            return $product->delete();
        });
    }

    public function get_best_selling_product()
    {
        $ids = [];
        $best_products = DB::table('order_items')->selectRaw('product_id, COUNT(*) as count')->groupBy('product_id')->orderBy('count', 'desc')->take(5)->get();
        foreach ($best_products as $product) { array_push($ids, $product->product_id); }
        return ProductResource::collection(Product::whereIn('id', $ids)->get());
    }
}

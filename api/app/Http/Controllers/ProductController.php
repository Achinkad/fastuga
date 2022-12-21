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

class ProductController extends Controller
{
    public function __construct()
    {
        //$this->middleware('can:viewAny')->only('viewAny');
        //$this->middleware('can:view')->only('view');
        //$this->middleware('can:create')->only('create');
        //$this->middleware('can:update')->only('update');
        //$this->middleware('can:delete')->only('delete');
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
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }

        $product = new Product;
        $product->fill($request->validated());

        // -> Stores Product Photo
        if ($request->has('photo_url') && $request->file('photo_url')->isValid()) {
            /*
            $photo = $request->file('photo_url');
            $photo_id = $photo->hashName();
            Storage::putFileAs('public/products', $photo, $photo_id);
            $product->photo_url = $photo_id;
            */
            $folderPath = "public/products/";

            $image_parts = explode(";base64,", $product->photo_url);

            $image_type_aux = explode("image/", $image_parts[0]);

            $image_type = $image_type_aux[1];

            $image_base64 = base64_decode($image_parts[1]);


            $uniqid=uniqid();


            $file="{$uniqid}.{$image_type}";

            // -> Stores the new photo

            Storage::put($folderPath.$file, $image_base64);


            $product->photo_url = $file;

        }
        else{
            $product->photo_url="product-none.png";

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
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }

        $product->fill($request->validated());

        if ($request->has('photo_url')) {
            // -> Check if a previous file exists and deletes it
            if(Storage::disk('public')->exists($product->photo_url)) {
                Storage::delete($product->photo_url);
            }
            /*
            // -> Stores the new photo
            $photo = $request->file('photo_url');
            $photo_id = $photo->hashName();
            Storage::putFileAs('public/products', $photo, $photo_id);
            $product->photo_url = $photo_id;
            */
            $folderPath = "public/products/";

            $image_parts = explode(";base64,", $product->photo_url);

            $image_type_aux = explode("image/", $image_parts[0]);

            $image_type = $image_type_aux[1];

            $image_base64 = base64_decode($image_parts[1]);


            $uniqid=uniqid();


            $file="{$uniqid}.{$image_type}";

            // -> Stores the new photo

            Storage::put($folderPath.$file, $image_base64);


            $product->photo_url = $file;
        }

        $product->save();
        return new ProductResource($product);
    }

    public function destroy($id) // -> Boolean Return
    {
        if (Auth()->guard('api')->user()->type != "EM") { abort(403); }

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

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
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
        $this->middleware('auth.manager', ['except' => [
            'index',
            'show',
            'store',
            'update',
            'destroy'
        ]]);
    }

    public function index(Request $request)
    {
        $products = $request->has('type') ? Product::where('type', $request->input('type'))->paginate(10) : Product::paginate(10);
        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        // -> Stores Product Photo
        if ($request->has('photo_url') & $request->file('photo_url')->isValid()) {
            $photo = $request->file('photo_url');
            $photo_id = $photo->hashName();
            Storage::disk('public')->putFileAs('products/', $photo, $photo_id);
            $product->photo_url = $photo_id;
            $product->save();
        }

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
            // -> Stores the new photo
            $photo = $request->file('photo_url');
            $photo_id = $photo->hashName() . '.' . $photo->extension();
            $filesystem = Storage::disk('public');
            $filesystem->putFileAs('products/', $photo, $photo_id);
            //Storage::disk('public')->putFileAs('products/', $photo, $photo_id);
            $product->photo_url = $photo_id;
        }

        $product->save();
        return new ProductResource($product);
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $product = Product::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
            if ($product->order_item) {
                $product->order_item->detach();
            }
            return $product->delete();
        });
    }
}

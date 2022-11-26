<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(Product::all());
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        // -> Stores Product Photo
        if ($request->has('photo_url')) {
            $photo = $request->file('photo_url');
            $photo_id = $photo->hashName() . '.' . $photo->extension();
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
            Storage::disk('public')->putFileAs('products/', $photo, $photo_id);
            $product->photo_url = $photo_id;
        }

        $product->save();
        return new ProductResource($product);
    }

    public function destroy($id) // -> Boolean Return
    {
        return DB::transaction(function () use ($id) {
            $user = Product::where(['id' => $id], ['deleted_at' => null])->firstOrFail();
            if ($product->order_item) { $product->order_item->detach(); }
            return $product->delete();
        });
    }
}

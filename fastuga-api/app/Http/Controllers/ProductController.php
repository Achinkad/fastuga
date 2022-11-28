<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(Product::paginate(10));
    }

    public function store(ValidateNewProduct $request)
    {
        /*
        $newPhoto = $request->file('photo_url');
        $newProduct = $request->only('name', 'type', 'description', 'photo_url', 'price');

        $product = new Product([
            'name' => $newProduct["name"],
            'type' => $newProduct["type"],
            'description' => $newProduct["description"],
            'price' => $newProduct["price"],
        ]);

        if($newPhoto){
            $filename = Storage::putFileAs('public/products', $request->photo_url, time() . '.' . $newPhoto->getClientOriginalExtension());

            $filename = substr($filename, strrpos($filename, '/')+1, strlen($filename));
            $product->photo_url = $filename;
        }

        
        if ($product->save()) {
            return response()->json("Produto adicionado com sucesso", 200);
        }else {
            return response()->json("Não foi possível adicionar o produto", 404);
        }
        */
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    public function update(ValidateNewProduct $request, Product $product)
    {
        /*
        $newPhoto = $request->file('photo_url');
        $newProduct = $request->only('id','name', 'type', 'description', 'photo_url', 'price');
        $product = Product::find($id);

        $product->name = $newProduct["name"];
        $product->type = $newProduct["type"];
        $product->description = $newProduct["description"];
        $product->price = $newProduct["price"];


        if($newPhoto){
            $filename = Storage::putFileAs('public/products', $request->photo_url, time() . '.' . $newPhoto->getClientOriginalExtension());

            $filename = substr($filename, strrpos($filename, '/')+1, strlen($filename));
            $product->photo_url = $filename;
        }

        
        if ($product->save()) {
            return response()->json("Produto atualizado com sucesso", 200);
        }else {
            return response()->json("Não foi possível atualizar o produto", 404);
        }
        */
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return new ProductResource($product);
    }
}

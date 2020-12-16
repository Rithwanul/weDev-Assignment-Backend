<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Products;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Validator;


class ProductsController extends Controller
{
    /**
     * Create Product
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function Create(Request $request)
    {

        $rules = [
            'title' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|image|mimes:png,jpg|max:2048',
            'price' => 'required|string'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $product = new Products();
        $product->title         =       $request->title;
        $product->description   =       $request->description;
        $product->price         =       (double)$request->price;
        $product->image         =       Str::random(60) . '.' . $request->image->extension();
        $path                   =       $request->file('image')
            ->move('public/products', $product->image)
            ->getFilename();
        $product->save();

        return response()->json([
            "success"   =>      true,
            "message"   =>      "File successfully uploaded"
        ]);
    }
}

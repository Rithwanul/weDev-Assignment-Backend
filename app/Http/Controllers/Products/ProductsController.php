<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isNull;
use Validator;


class ProductsController extends Controller
{
    /**
     * Get all products
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function Index(){
        $products       =       Products::all();

        if (count($products) > 0){
            return response()->json($products, 201);
        }

        return response()->json([
            'message'       =>          'No Data is found'
        ]);
    }

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

    /**
     * Delete Product
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse [string] message
     */
    public function Delete($id){
        $product = Products::find($id);

        if ($product != null)
            $result  = $product->delete();
        else
            return response()->json([
                'error'     =>      'Record Not Found'
            ], 404);

        if ($result)
            return response()->json($result, 200);

        return  response()->json([
            'error'     =>      'Record Not Found'
        ], 404);
    }

    /**
     * Update Product
     *
     * @param $id
     * @param Request $request
     * @return void [string] message
     */
    public function Update(Request $request, $id){
        $product = Products::find($id);

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


        $product->title         =       $request->title;
        $product->description   =       $request->description;
        $product->price         =       (double)$request->price;
        $product->image         =       Str::random(60) . '.' . $request->image->extension();
        $path                   =       $request->file('image')
                                                ->move('public/products', $product->image)
                                                ->getFilename();
        $result  =   $product->save();

        return response()->json([
            'success'       =>          $result
        ], 201);
    }
}

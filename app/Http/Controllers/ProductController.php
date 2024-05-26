<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // อ่านรายการสินค้าทั้งหมด
    public function index()
    {
        // Read all products
        return Product::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if ($user->tokenCan("1")) {

            // Validate form
            $request->validate([
                'name' => 'required|min:3',
                'slug' => 'required',
                'price' => 'required'
            ]);

            // กำหนดตัวแปรรับค่าจากฟอร์ม
            $data_product = array(
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'price' => $request->input('price'),
                'user_id' => $user->id
            );

            // Create data to tabale product
            $product = Product::create($data_product);

            $response = [
                'status' => true,
                'message' => "Product created successfully",
                'product' => $product,
            ];

            return response($response, 201);

        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to create'
            ], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);

        if ($product) {
            return response([
                'status' => true,
                'product' => $product
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if ($user->tokenCan("1")) {

            $request->validate([
                'name' => 'required',
                'slug' => 'required',
                'price' => 'required'
            ]);

            $data_product = array(
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'price' => $request->input('price'),
                'user_id' => $user->id
            );

            $product = Product::find($id);
            $product->update($data_product);

            return response([
                'status' => true,
                'message' => 'Product updated successfully',
                'product' => $product
            ]);

        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to create'
            ], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if ($user->tokenCan("1")) {

            $product = Product::destroy($id);

            if ($product) {
                return response([
                    'status' => true,
                    'message' => 'Product deleted successfully'
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to create'
            ], 401);
        }

    }
}


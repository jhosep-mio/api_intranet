<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
 
    public function index()
    {
        $productos = Product::all();
        return $productos;
    }

    public function store(Request $request)
    {
        $saveproducto = new Product();
        $request->validate([
            'nombre'=>'required',
            'categoria'=>'required',
            'imagen' =>'required|image'
        ]);

        $saveproducto->nombre = $request->nombre;
        $saveproducto->categoria = $request->categoria;
        
        if($request->hasFile('imagen')){
            $file = $request->file('imagen');
            $filename = $file->getClientOriginalName(); 
            $name_File=str_replace(" ","_", $filename);
            
            $picture = date('His').'-'.$name_File;
            $file->move(public_path('productos/'),$picture);
        }  

        $saveproducto->imagen = $picture;
        
        $result = $saveproducto->save();

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }


    public function show($id)
    {
        $verproducto = Product::find($id);
        return $verproducto;
    }

    public function update(Request $request, $id)
    {
        $updateproducto= Product::findOrFail($id);

        $request->validate([
            'nombre'=>'required',
            'categoria'=>'required',
        ]);

        $updateproducto->nombre = $request->nombre;
        $updateproducto->categoria = $request->categoria;

        if($request->hasFile('imagen')){

            $file = $request->file('imagen');
            $filename = $file->getClientOriginalName(); 
            $name_File=str_replace(" ","_", $filename);
            
            $picture = date('His').'-'.$name_File;
            $file->move(public_path('productos/'),$picture);
            $updateproducto->imagen = $picture;

            $producto =  Product::find($id);
            unlink(public_path('productos/'.$producto -> imagen));
            
        }else {
            $verproducto = Product::find($id);
            $picture = $verproducto['imagen'];
        }

        $updateproducto->imagen = $picture;
        $result =$updateproducto->save();

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }

    public function destroy($id)
    {

        $producto =  Product::find($id);
        unlink(public_path('productos/'.$producto -> imagen));

        $result = Product::destroy($id);

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }

    }
}
?>
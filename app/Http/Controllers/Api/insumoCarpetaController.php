<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\insumoCarpeta;
use Illuminate\Http\Request;

class insumoCarpetaController extends Controller
{
    public function show($id){
        $vetCatServicios = insumoCarpeta::find($id);
        return $vetCatServicios;
    }

    public function update(Request $request, $id){
        $updateClinica= insumoCarpeta::findOrFail($id);

        $validatedData = $request->validate([
            'nombre'=>'required|string',
            'precio'=>'required',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $updateClinica->nombre = $validatedData['nombre'];
        $updateClinica->precio = $validatedData['precio'];
        $result =$updateClinica->save();

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }
}

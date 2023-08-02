<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\catservicios;
use Illuminate\Http\Request;

class catserviciosController extends Controller
{
    public function index(){
        $catservicios = catservicios::orderBy('catservicios.id', 'asc')->get();
        return $catservicios;
    }

    public function store(Request $request){
        $saveCatServicios = new catservicios();

        $request->validate([
            'insumoUSB'=>'required',
            'nombre'=>'required|string',
            'impreso'=>'required|integer',
        ]);

        $saveCatServicios->insumoUSB = $request->insumoUSB;
        $saveCatServicios->nombre = $request->nombre;
        $saveCatServicios->impreso = $request->impreso;
        
        $result = $saveCatServicios->save();

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }
    
    public function show($id){
        $vetCatServicios = catservicios::find($id);
        return $vetCatServicios;
    }


    public function update(Request $request, $id){
        $updateCatServicios= catservicios::findOrFail($id);

         $request->validate([
            'insumoUSB'=>'required',
            'nombre'=>'required|string',
            'impreso'=>'required|integer',
        ]);
        
        $updateCatServicios->insumoUSB = $request->insumoUSB;
        $updateCatServicios->nombre = $request->nombre;
        $updateCatServicios->impreso = $request->impreso;

        $result =$updateCatServicios->save();

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }

    public function destroy($id){
        $result = catservicios::destroy($id);

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }

    }
}

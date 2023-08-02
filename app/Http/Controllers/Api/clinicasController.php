<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\clinicas;
use Illuminate\Http\Request;

class clinicasController extends Controller
{
    public function index(){
        $registros = clinicas::where('id', '!=', '0')
                    ->orderBy('clinicas.id', 'desc')
                    ->get();
        return $registros;
    }

    public function buscar(Request $request) {
        $resultados = clinicas::where('nombre', 'LIKE', '%' . $request->buscar . '%')
                                ->orWhere('id', 'LIKE', '%' . $request->buscar . '%')
                                ->orWhere('direccion', 'LIKE', '%' . $request->buscar . '%')
                                ->orWhere('telefono', 'LIKE', '%' . $request->buscar . '%')
                                ->orWhere('celular', 'LIKE', '%' . $request->buscar . '%')
                                ->orderBy('clinicas.id', 'desc')
                                ->get();
        return response()->json($resultados);
    }

    public function index2(){
        $registros = clinicas::orderBy('clinicas.id', 'desc')
                    ->get();
        return $registros;
    }

    public function show($id){
        $verClinica = clinicas::find($id);
        return $verClinica;
    }

    public function store2(Request $request){
        $saveclinica = new clinicas();

        $validatedData = $request->validate([
            'nombre'=>'required|string',
            'direccion'=>'nullable',
            'referencia' =>'string|nullable',
            'telefono' => [
                'nullable',
                'regex:/^[0-9]{7,9}$/',
            ],
            'celular' => [
                'nullable',
                'regex:/^[0-9]{7,9}$/',
            ],
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $saveclinica->nombre = $validatedData['nombre'];
        $saveclinica->direccion = $validatedData['direccion'];
        $saveclinica->referencia = $validatedData['referencia'];
        $saveclinica->telefono = $validatedData['telefono'];
        $saveclinica->celular = $validatedData['celular'];
        
        $result = $saveclinica->save();

        if($result){
            return response()->json(['status'=>"success", "res" => $saveclinica->id, 'res_nombre' => $saveclinica->nombre]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }

    public function update(Request $request, $id){
        $updateClinica= clinicas::findOrFail($id);

        $validatedData = $request->validate([
            'nombre'=>'required|string',
            'direccion'=>'nullable',
            'referencia' =>'string|nullable',
            'telefono' => [
                'nullable',
                'regex:/^[0-9]{7,9}$/',
            ],
            'celular' => [
                'nullable',
                'regex:/^[0-9]{7,9}$/',
            ],
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $updateClinica->nombre = $validatedData['nombre'];
        $updateClinica->direccion = $validatedData['direccion'];
        $updateClinica->referencia = $validatedData['referencia'];
        $updateClinica->telefono = $validatedData['telefono'];
        $updateClinica->celular = $validatedData['celular'];

        $result =$updateClinica->save();

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }
    
    public function destroy($id){
        $result = clinicas::destroy($id);

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }

    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\egresos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class egresosController extends Controller
{
    public function index(){
        $catservicios = egresos::orderBy('egresos.id', 'desc')->get();
        return $catservicios;
    }

    public function buscar(Request $request) {
        $resultados = egresos::where('descripcion', 'LIKE', '%' . $request->buscar . '%')
                                ->orderBy('id', 'desc')
                                ->get();
        return response()->json($resultados);
    }

    public function reporte(){
        $catservicios = egresos::orderBy('egresos.id', 'asc')
                                ->get();
        return $catservicios;
    }


    public function reporteMes(){
        $primerDiaMesPasado = now()->subMonth()->startOfMonth();
        $ultimoDiaMesPasado = now()->subMonth()->endOfMonth();

        $catservicios = egresos::orderBy('egresos.id', 'asc')
                                ->whereBetween(DB::raw('DATE(egresos.created_at)'), [$primerDiaMesPasado, $ultimoDiaMesPasado])
                                ->get();
        return $catservicios;
    }



    public function reporteFecha(Request $request){
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $fechaInicio .= ' 00:00:00';
        $fechaFin .= ' 23:59:59';

        $catservicios = egresos::orderBy('egresos.id', 'asc')
                                ->whereBetween('egresos.created_at', [$fechaInicio, $fechaFin])
                                ->get();
        return $catservicios;
    }

    public function store(Request $request){
        $saveclinica = new egresos();

        $validatedData = $request->validate([
            'descripcion'=>'required|string',
            'cantidad'=>'required',
            'total' =>'required',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $saveclinica->descripcion = $validatedData['descripcion'];
        $saveclinica->cantidad = $validatedData['cantidad'];
        $saveclinica->total = $validatedData['total'];

        $result = $saveclinica->save();

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }
    
    public function show($id){
        $verClinica = egresos::find($id);
        return $verClinica;
    }

    public function update(Request $request, $id){
        $updateClinica= egresos::findOrFail($id);

        $validatedData = $request->validate([
            'descripcion'=>'required|string',
            'cantidad'=>'required',
            'total' =>'required',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $updateClinica->descripcion = $validatedData['descripcion'];
        $updateClinica->cantidad = $validatedData['cantidad'];
        $updateClinica->total = $validatedData['total'];


        $result =$updateClinica->save();

        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }

    public function destroy($id){
        $result = egresos::destroy($id);
        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }
}

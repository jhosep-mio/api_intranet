<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\facturas;
use Illuminate\Http\Request;

class facturasController extends Controller
{

    public function index()
    {
        $registros = facturas::orderBy('facturas.id', 'desc')
            ->get();
        return $registros;
    }

    public function indexID($id)
    {
        $registros = facturas::orderBy('facturas.id', 'desc')
            ->where('id_orden', '=', $id)
            ->get();
        return $registros;
    }

    private function contarOrdenesRegistradas($idOrden)
    {
        // Cuenta las facturas con el mismo ID de orden
        $contador = facturas::where('id_orden', $idOrden)->count();
        return $contador;
    }


    public function store(Request $request)
    {
        $saveclinica = new facturas();

        $validatedData = $request->validate([
            'id_orden' => 'required|integer',
            'tipo_documento' => 'required|integer',
            'id_documento' => 'required|string',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $contadorOrdenes = $this->contarOrdenesRegistradas($validatedData['id_orden']);

        $validatedData['estado'] = $contadorOrdenes + 1;

        $saveclinica->id_orden = $validatedData['id_orden'];
        $saveclinica->tipo_documento = $validatedData['tipo_documento'];
        $saveclinica->id_documento = $validatedData['id_documento'];
        $saveclinica->estado = $validatedData['estado'];
        

        $result = $saveclinica->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function show($id)
    {
        $verClinica = facturas::find($id);
        return $verClinica;
    }

    public function update(Request $request, $id)
    {
        $updateClinica = facturas::findOrFail($id);

        $validatedData = $request->validate([
            'id_orden' => 'required|integer',
            'tipo_documento' => 'required|integer',
            'id_documento' => 'required|string',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $updateClinica->id_orden = $validatedData['id_orden'];
        $updateClinica->tipo_documento = $validatedData['tipo_documento'];
        $updateClinica->id_documento = $validatedData['id_documento'];

        $result = $updateClinica->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }
}

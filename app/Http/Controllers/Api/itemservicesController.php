<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\itemservices;
use App\Models\ordenes;
use Illuminate\Http\Request;

class itemservicesController extends Controller
{
    public function index()
    {
        $items = itemservices::join('catservicios', 'itemservices.id_servicio', '=', 'catservicios.id')
            ->select(
                'itemservices.*',
                'catservicios.nombre as servicio'
            )
            ->orderBy('itemservices.id', 'desc')
            ->get();
        return $items;
    }

    public function store(Request $request)
    {
        $saveItem = new itemservices();

        $validatedData = $request->validate([
            'id_servicio' => 'required|integer',
            'nombre' => 'required|string',
            'precio_impresion' => 'required|numeric',
            'precio_digital' => 'required|numeric',
            'comision_impreso' => 'required|numeric',
            'comision_digital' => 'required|numeric',
            'insumos1' => 'nullable|numeric',
            'insumos2' => 'nullable|numeric',
            'insumos3' => 'nullable|numeric',
            'insumos4' => 'nullable|numeric',

            'insumosM1' => 'nullable|numeric',
            'insumosM2' => 'nullable|numeric',
            'insumosM3' => 'nullable|numeric',
            'insumosM4' => 'nullable|numeric',
            'insumosM5' => 'nullable|numeric',
            'insumosM6' => 'nullable|numeric',
            'insumoCarpeta' => 'nullable|numeric',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $saveItem->id_servicio = $validatedData['id_servicio'];
        $saveItem->nombre = $validatedData['nombre'];
        $saveItem->precio_impresion = $validatedData['precio_impresion'];
        $saveItem->precio_digital = $validatedData['precio_digital'];
        $saveItem->comision_impreso = $validatedData['comision_impreso'];
        $saveItem->comision_digital = $validatedData['comision_digital'];
        $saveItem->insumos1 = $validatedData['insumos1'];
        $saveItem->insumos2 = $validatedData['insumos2'];
        $saveItem->insumos3 = $validatedData['insumos3'];
        $saveItem->insumos4 = $validatedData['insumos4'];

        $saveItem->insumosM1 = $validatedData['insumosM1'];
        $saveItem->insumosM2 = $validatedData['insumosM2'];
        $saveItem->insumosM3 = $validatedData['insumosM3'];
        $saveItem->insumosM4 = $validatedData['insumosM4'];
        $saveItem->insumosM5 = $validatedData['insumosM5'];
        $saveItem->insumosM6 = $validatedData['insumosM6'];
        $saveItem->insumoCarpeta = $validatedData['insumoCarpeta'];

        $result = $saveItem->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function show($id)
    {
        $verServicio = itemservices::find($id);
        return $verServicio;
    }


    public function buscar(Request $request)
    {
        $resultados = itemservices::join('catservicios', 'itemservices.id_servicio', '=', 'catservicios.id')
            ->select(
                'itemservices.*',
                'catservicios.nombre as servicio'
            )
            ->where('itemservices.nombre', 'LIKE', '%' . $request->buscar . '%')
            ->orWhere('catservicios.nombre', 'LIKE', '%' . $request->buscar . '%')
            ->orWhere('itemservices.id', 'LIKE', '%' . $request->buscar . '%')
            ->orWhere('itemservices.precio_impresion', 'LIKE', '%' . $request->buscar . '%')
            ->orWhere('itemservices.precio_digital', 'LIKE', '%' . $request->buscar . '%')
            ->orderBy('itemservices.id', 'desc')
            ->get();

        return response()->json($resultados);
    }

    public function update(Request $request, $id)
    {
        $updateItem = itemservices::findOrFail($id);

        $validatedData = $request->validate([
            'id_servicio' => 'required|integer',
            'nombre' => 'required|string',
            'precio_impresion' => 'required|numeric',
            'precio_digital' => 'required|numeric',
            'comision_impreso' => 'required|numeric',
            'comision_digital' => 'required|numeric',
            'insumos1' => 'nullable|numeric',
            'insumos2' => 'nullable|numeric',
            'insumos3' => 'nullable|numeric',
            'insumos4' => 'nullable|numeric',

            'insumosM1' => 'nullable|numeric',
            'insumosM2' => 'nullable|numeric',
            'insumosM3' => 'nullable|numeric',
            'insumosM4' => 'nullable|numeric',
            'insumosM5' => 'nullable|numeric',
            'insumosM6' => 'nullable|numeric',
            'insumoCarpeta' => 'nullable|numeric',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);


        $updateItem->id_servicio = $validatedData['id_servicio'];
        $updateItem->nombre = $validatedData['nombre'];

        $updateItem->precio_impresion = $validatedData['precio_impresion'];
        $updateItem->precio_digital = $validatedData['precio_digital'];

        $updateItem->comision_impreso = $validatedData['comision_impreso'];
        $updateItem->comision_digital = $validatedData['comision_digital'];
        $updateItem->insumos1 = $validatedData['insumos1'];
        $updateItem->insumos2 = $validatedData['insumos2'];
        $updateItem->insumos3 = $validatedData['insumos3'];
        $updateItem->insumos4 = $validatedData['insumos4'];

        $updateItem->insumosM1 = $validatedData['insumosM1'];
        $updateItem->insumosM3 = $validatedData['insumosM3'];
        $updateItem->insumosM2 = $validatedData['insumosM2'];
        $updateItem->insumosM4 = $validatedData['insumosM4'];
        $updateItem->insumosM5 = $validatedData['insumosM5'];
        $updateItem->insumosM6 = $validatedData['insumosM6'];
        $updateItem->insumoCarpeta = $validatedData['insumoCarpeta'];

        $result = $updateItem->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function destroy($id)
    {
        // Verificar si el item existe en alguna listaItems de órdenes
        $ordersWithItem = ordenes::whereRaw('JSON_CONTAINS(listaItems, \'{"id_item": ' . $id . ', "estado": true}\')')->get();

        if ($ordersWithItem->isNotEmpty()) {
            // El item está relacionado en al menos una orden, no se puede eliminar
            return response()->json(['status' => 'error', 'message' => 'item usado.']);
        }

        // El item no está relacionado en ninguna orden, se puede eliminar
        $result = itemservices::destroy($id);

        if ($result) {
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'No se pudo eliminar el item.']);
        }
    }
}

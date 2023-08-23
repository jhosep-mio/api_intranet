<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\clinicas;
use App\Models\odontologos;
use App\Models\ordenes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenesController extends Controller
{

    public function index()
    {
        $startOfDay = now()->startOfDay(); // Obtiene el inicio del día actual (00:00:00)
        $endOfDay = now()->endOfDay(); // Obtiene el final del día actual (23:59:59)

        $ordenes = ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )
            ->whereBetween('ordenes.created_at', [$startOfDay, $endOfDay])
            ->orderBy('ordenes.id', 'desc')
            ->get();
        return $ordenes;
    }

    public function indexPerMes()
    {
        $ordenes = ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )
            ->orderBy('ordenes.id', 'desc')
            ->get();
        return $ordenes;
    }

    private function quitarAcentos($cadena)
    {
        $acentos = array(
            'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú',
            'à', 'è', 'ì', 'ò', 'ù', 'À', 'È', 'Ì', 'Ò', 'Ù',
            'ä', 'ë', 'ï', 'ö', 'ü', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü',
            'â', 'ê', 'î', 'ô', 'û', 'Â', 'Ê', 'Î', 'Ô', 'Û',
            'ã', 'õ', 'ñ', 'ç', 'Ã', 'Õ', 'Ñ', 'Ç'
        );

        $sinAcentos = array(
            'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U',
            'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U',
            'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U',
            'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U',
            'a', 'o', 'n', 'c', 'A', 'O', 'N', 'C'
        );

        $cadenaSinAcentos = str_replace($acentos, $sinAcentos, $cadena);

        return $cadenaSinAcentos;
    }

    public function buscar(Request $request)
    {
        $search = '%' . $this->quitarAcentos($request->buscar) . '%';
        $searchF = '%' . $request->buscarF . '%';

        $ordenes = Ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )

            ->where(function ($query) use ($search) {
                $query->whereRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellido_p, ' ', pacientes.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ?", [$search]);

                $nombres = explode(' ', $search);
                if (count($nombres) >= 2) {
                    $nombre = '%' . $this->quitarAcentos($nombres[0]) . '%';
                    $apellido = '%' . $this->quitarAcentos($nombres[1]) . '%';

                    $query->orWhereRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellido_p) COLLATE utf8mb4_unicode_ci LIKE ? AND CONCAT(pacientes.nombres, ' ', pacientes.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ?", [$nombre, $apellido]);
                    $query->orWhereRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ? AND CONCAT(pacientes.nombres, ' ', pacientes.apellido_p) COLLATE utf8mb4_unicode_ci LIKE ?", [$nombre, $apellido]);
                }

                // Búsqueda por nombres y apellidos del odontólogo
                $query->orWhereRaw("CONCAT(odontologos.nombres, ' ', odontologos.apellido_p, ' ', odontologos.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ?", [$search]);

                $odontologoNombres = explode(' ', $search);
                if (count($odontologoNombres) >= 2) {
                    $odontologoNombre = '%' . $this->quitarAcentos($odontologoNombres[0]) . '%';
                    $odontologoApellido = '%' . $this->quitarAcentos($odontologoNombres[1]) . '%';

                    $query->orWhereRaw("CONCAT(odontologos.nombres, ' ', odontologos.apellido_p) COLLATE utf8mb4_unicode_ci LIKE ? AND CONCAT(odontologos.nombres, ' ', odontologos.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ?", [$odontologoNombre, $odontologoApellido]);
                    $query->orWhereRaw("CONCAT(odontologos.nombres, ' ', odontologos.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ? AND CONCAT(odontologos.nombres, ' ', odontologos.apellido_p) COLLATE utf8mb4_unicode_ci LIKE ?", [$odontologoNombre, $odontologoApellido]);
                }
            })

            ->orWhere('odontologos.nombres', 'LIKE', $search)
            ->orWhere('odontologos.apellido_p', 'LIKE', $search)
            ->orWhere('odontologos.apellido_m', 'LIKE', $search)
            ->orWhere('ordenes.created_at', 'LIKE', $search)
            ->orWhere('odontologos.cop', 'LIKE', $search)
            ->orWhere('ordenes.id',  $request->buscar)
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($ordenes);
    }

    public function buscarFechas(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $fechaInicio .= ' 00:00:00';
        $fechaFin .= ' 23:59:59';

        $ordenes = ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )
            ->whereBetween('ordenes.created_at', [$fechaInicio, $fechaFin])
            ->orderBy('ordenes.id', 'desc')
            ->get();
        return response()->json($ordenes);
    }

    public function buscarCreaados(Request $request)
    {
        $ordenes = ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )
            ->where('ordenes.estado', '=', $request->input('estado'))
            ->orderBy('ordenes.id', 'desc')
            ->get();
        return response()->json($ordenes);
    }

    public function buscarCreaadoswhereFechas(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $fechaInicio .= ' 00:00:00';
        $fechaFin .= ' 23:59:59';

        $estado = $request->input('estado');

        $ordenes = Ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )
            ->where(function ($query) use ($estado, $fechaInicio, $fechaFin) {
                $query->where('ordenes.estado', '=', $estado)
                    ->whereBetween('ordenes.created_at', [$fechaInicio, $fechaFin]);
            })
            ->orderBy('ordenes.id', 'desc')
            ->get();

        return response()->json($ordenes);
    }


    public function indexClientes($id)
    {
        $ordenes = ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )
            ->where('ordenes.id_paciente', '=', $id)
            ->where('ordenes.estado', '=', 2)
            ->orderBy('ordenes.id', 'desc')
            ->get();
        return $ordenes;
    }


    public function indexDoctores($id)
    {
        $ordenes = ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )
            ->where('ordenes.id_odontologo', '=', $id)
            ->where('ordenes.estado', '=', 2)
            ->orderBy('ordenes.id', 'desc')
            ->get();
        return $ordenes;
    }

    public function store(Request $request)
    {

        $saveOrdenVirtual = new ordenes();

        $validatedData = $request->validate([
            'id_creacion' => 'nullable',
            'id_modificacion' => 'nullable',

            'id_paciente' => 'required|numeric',
            'id_odontologo' => 'required|numeric',
            'id_clinica' => 'required|numeric',
            'consulta' => 'nullable|string',

            'box18' => 'boolean',
            'box17' => 'boolean',
            'box16' => 'boolean',
            'box15' => 'boolean',
            'box14' => 'boolean',
            'box13' => 'boolean',
            'box12' => 'boolean',
            'box11' => 'boolean',
            'box21' => 'boolean',
            'box22' => 'boolean',
            'box23' => 'boolean',
            'box24' => 'boolean',
            'box25' => 'boolean',
            'box26' => 'boolean',
            'box27' => 'boolean',
            'box28' => 'boolean',
            'box48' => 'boolean',
            'box47' => 'boolean',
            'box46' => 'boolean',
            'box45' => 'boolean',
            'box44' => 'boolean',
            'box43' => 'boolean',
            'box42' => 'boolean',
            'box41' => 'boolean',
            'box31' => 'boolean',
            'box32' => 'boolean',
            'box33' => 'boolean',
            'box34' => 'boolean',
            'box35' => 'boolean',
            'box36' => 'boolean',
            'box37' => 'boolean',
            'box38' => 'boolean',

            'siConGuias' => 'boolean',
            'noConGuias' => 'boolean',

            'listaServicios' => 'nullable|string',
            'impresionServicios' => 'nullable|string',
            'arryServicios' => 'nullable|string',
            'listaItems' => 'required|string',
            'metodoPago' => 'nullable|numeric',
            'precio_final' => 'required|numeric',

            'otrosAnalisis' => 'nullable|string',
            'estado' => 'required|numeric',
            'activeComision' => 'required|numeric',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $saveOrdenVirtual->id_creacion = $validatedData['id_creacion'];
        $saveOrdenVirtual->id_modificacion = $validatedData['id_modificacion'];

        $saveOrdenVirtual->id_paciente = $validatedData['id_paciente'];
        $saveOrdenVirtual->id_odontologo = $validatedData['id_odontologo'];
        $saveOrdenVirtual->id_clinica = $validatedData['id_clinica'];
        $saveOrdenVirtual->consulta = $validatedData['consulta'];


        $saveOrdenVirtual->box18 = $validatedData['box18'];
        $saveOrdenVirtual->box17 = $validatedData['box17'];
        $saveOrdenVirtual->box16 = $validatedData['box16'];
        $saveOrdenVirtual->box15 = $validatedData['box15'];
        $saveOrdenVirtual->box14 = $validatedData['box14'];
        $saveOrdenVirtual->box13 = $validatedData['box13'];
        $saveOrdenVirtual->box12 = $validatedData['box12'];
        $saveOrdenVirtual->box11 = $validatedData['box11'];

        $saveOrdenVirtual->box21 = $validatedData['box21'];
        $saveOrdenVirtual->box22 = $validatedData['box22'];
        $saveOrdenVirtual->box23 = $validatedData['box23'];
        $saveOrdenVirtual->box24 = $validatedData['box24'];
        $saveOrdenVirtual->box25 = $validatedData['box25'];
        $saveOrdenVirtual->box26 = $validatedData['box26'];
        $saveOrdenVirtual->box27 = $validatedData['box27'];
        $saveOrdenVirtual->box28 = $validatedData['box28'];


        $saveOrdenVirtual->box48 = $validatedData['box48'];
        $saveOrdenVirtual->box47 = $validatedData['box47'];
        $saveOrdenVirtual->box46 = $validatedData['box46'];
        $saveOrdenVirtual->box45 = $validatedData['box45'];
        $saveOrdenVirtual->box44 = $validatedData['box44'];
        $saveOrdenVirtual->box43 = $validatedData['box43'];
        $saveOrdenVirtual->box42 = $validatedData['box42'];
        $saveOrdenVirtual->box41 = $validatedData['box41'];

        $saveOrdenVirtual->box31 = $validatedData['box31'];
        $saveOrdenVirtual->box32 = $validatedData['box32'];
        $saveOrdenVirtual->box33 = $validatedData['box33'];
        $saveOrdenVirtual->box34 = $validatedData['box34'];
        $saveOrdenVirtual->box35 = $validatedData['box35'];
        $saveOrdenVirtual->box36 = $validatedData['box36'];
        $saveOrdenVirtual->box37 = $validatedData['box37'];
        $saveOrdenVirtual->box38 = $validatedData['box38'];

        $saveOrdenVirtual->siConGuias = $validatedData['siConGuias'];
        $saveOrdenVirtual->noConGuias = $validatedData['noConGuias'];

        $saveOrdenVirtual->listaServicios = $validatedData['listaServicios'];
        $saveOrdenVirtual->impresionServicios = $validatedData['impresionServicios'];
        $saveOrdenVirtual->arryServicios = $validatedData['arryServicios'];
        $saveOrdenVirtual->listaItems = $validatedData['listaItems'];
        $saveOrdenVirtual->metodoPago = $validatedData['metodoPago'];
        $saveOrdenVirtual->precio_final = $validatedData['precio_final'];

        $saveOrdenVirtual->otrosAnalisis = $validatedData['otrosAnalisis'];
        $saveOrdenVirtual->estado = $validatedData['estado'];
        $saveOrdenVirtual->activeComision = $validatedData['activeComision'];

        $result = $saveOrdenVirtual->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function show($id)
    {
        $verOrden = ordenes::find($id);

        if ($verOrden) {
            return response()->json([
                'status' => "success",
                'verOrden' => $verOrden
            ]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function update(Request $request, $id)
    {

        $updateOrdenVirtual = ordenes::findOrFail($id);

        $validatedData = $request->validate([
            'id_modificacion' => 'nullable',

            'id_paciente' => 'required|numeric',
            'id_clinica' => 'required|numeric',
            'id_odontologo' => 'required|numeric',
            'consulta' => 'nullable|string',

            'box18' => 'boolean',
            'box17' => 'boolean',
            'box16' => 'boolean',
            'box15' => 'boolean',
            'box14' => 'boolean',
            'box13' => 'boolean',
            'box12' => 'boolean',
            'box11' => 'boolean',
            'box21' => 'boolean',
            'box22' => 'boolean',
            'box23' => 'boolean',
            'box24' => 'boolean',
            'box25' => 'boolean',
            'box26' => 'boolean',
            'box27' => 'boolean',
            'box28' => 'boolean',
            'box48' => 'boolean',
            'box47' => 'boolean',
            'box46' => 'boolean',
            'box45' => 'boolean',
            'box44' => 'boolean',
            'box43' => 'boolean',
            'box42' => 'boolean',
            'box41' => 'boolean',
            'box31' => 'boolean',
            'box32' => 'boolean',
            'box33' => 'boolean',
            'box34' => 'boolean',
            'box35' => 'boolean',
            'box36' => 'boolean',
            'box37' => 'boolean',
            'box38' => 'boolean',

            'siConGuias' => 'boolean',
            'noConGuias' => 'boolean',

            'listaServicios' => 'nullable|string',
            'impresionServicios' => 'nullable|string',
            'arryServicios' => 'nullable|string',
            'listaItems' => 'required|string',
            'metodoPago' => 'nullable|numeric',
            'precio_final' => 'required|numeric',

            'otrosAnalisis' => 'nullable|string',
            'estado' => 'required|numeric',
            'activeComision' => 'required|numeric',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $updateOrdenVirtual->id_modificacion = $validatedData['id_modificacion'];

        $updateOrdenVirtual->id_paciente = $validatedData['id_paciente'];
        $updateOrdenVirtual->id_odontologo = $validatedData['id_odontologo'];
        $updateOrdenVirtual->id_clinica = $validatedData['id_clinica'];
        $updateOrdenVirtual->consulta = $validatedData['consulta'];


        $updateOrdenVirtual->box18 = $validatedData['box18'];
        $updateOrdenVirtual->box17 = $validatedData['box17'];
        $updateOrdenVirtual->box16 = $validatedData['box16'];
        $updateOrdenVirtual->box15 = $validatedData['box15'];
        $updateOrdenVirtual->box14 = $validatedData['box14'];
        $updateOrdenVirtual->box13 = $validatedData['box13'];
        $updateOrdenVirtual->box12 = $validatedData['box12'];
        $updateOrdenVirtual->box11 = $validatedData['box11'];

        $updateOrdenVirtual->box21 = $validatedData['box21'];
        $updateOrdenVirtual->box22 = $validatedData['box22'];
        $updateOrdenVirtual->box23 = $validatedData['box23'];
        $updateOrdenVirtual->box24 = $validatedData['box24'];
        $updateOrdenVirtual->box25 = $validatedData['box25'];
        $updateOrdenVirtual->box26 = $validatedData['box26'];
        $updateOrdenVirtual->box27 = $validatedData['box27'];
        $updateOrdenVirtual->box28 = $validatedData['box28'];


        $updateOrdenVirtual->box48 = $validatedData['box48'];
        $updateOrdenVirtual->box47 = $validatedData['box47'];
        $updateOrdenVirtual->box46 = $validatedData['box46'];
        $updateOrdenVirtual->box45 = $validatedData['box45'];
        $updateOrdenVirtual->box44 = $validatedData['box44'];
        $updateOrdenVirtual->box43 = $validatedData['box43'];
        $updateOrdenVirtual->box42 = $validatedData['box42'];
        $updateOrdenVirtual->box41 = $validatedData['box41'];

        $updateOrdenVirtual->box31 = $validatedData['box31'];
        $updateOrdenVirtual->box32 = $validatedData['box32'];
        $updateOrdenVirtual->box33 = $validatedData['box33'];
        $updateOrdenVirtual->box34 = $validatedData['box34'];
        $updateOrdenVirtual->box35 = $validatedData['box35'];
        $updateOrdenVirtual->box36 = $validatedData['box36'];
        $updateOrdenVirtual->box37 = $validatedData['box37'];
        $updateOrdenVirtual->box38 = $validatedData['box38'];

        $updateOrdenVirtual->siConGuias = $validatedData['siConGuias'];
        $updateOrdenVirtual->noConGuias = $validatedData['noConGuias'];

        $updateOrdenVirtual->listaServicios = $validatedData['listaServicios'];
        $updateOrdenVirtual->impresionServicios = $validatedData['impresionServicios'];
        $updateOrdenVirtual->arryServicios = $validatedData['arryServicios'];
        $updateOrdenVirtual->listaItems = $validatedData['listaItems'];
        $updateOrdenVirtual->metodoPago = $validatedData['metodoPago'];
        $updateOrdenVirtual->precio_final = $validatedData['precio_final'];

        $updateOrdenVirtual->otrosAnalisis = $validatedData['otrosAnalisis'];
        $updateOrdenVirtual->estado = $validatedData['estado'];
        $updateOrdenVirtual->activeComision = $validatedData['activeComision'];

        $result = $updateOrdenVirtual->save();
        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function updateUserToModificate(Request $request, $id)
    {

        $updateOrdenVirtual = ordenes::findOrFail($id);

        $validatedData = $request->validate([
            'id_modificacion' => 'required',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $updateOrdenVirtual->id_modificacion = $validatedData['id_modificacion'];
        $result = $updateOrdenVirtual->save();
        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function updateFactura(Request $request, $id)
    {

        $updateOrdenVirtual = ordenes::findOrFail($id);

        $validatedData = $request->validate([
            'estado' => 'required',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $updateOrdenVirtual->estado = $validatedData['estado'];

        $result = $updateOrdenVirtual->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function verificacion(Request $request)
    {
        $orden = ordenes::where("id_paciente", "=", $request->id_paciente)
            ->where("id_odontologo", "=", $request->id_odontologo)
            ->where("estado", "=", 0)
            ->first();

        if ($orden) {
            return response()->json([
                "status" => "succes",
                "message" => "orden_creada",
                "npx" => $orden->id
            ]);
        } else {
            return response()->json([
                "status" => "succes",
                "message" => "orde_no_creada"
            ]);
        }
    }
    public function obtenerClientesOdontologosMes()
    {
        $primerDiaMesActual = Carbon::now()->startOfMonth()->startOfDay();
        $ultimoDiaMesActual = Carbon::now()->endOfMonth()->endOfDay();

        $clinicas = clinicas::select(
            'clinicas.nombre',
            'odontologos.nombres as odontologo',
            'odontologos.apellido_p as apellido_p',
            'odontologos.apellido_m as apellido_m',
            'odontologos.celular as celular',
            DB::raw('COUNT(ordenes.id) as ordenes_count'),
            DB::raw('DATE_FORMAT(ordenes.created_at, "%Y-%m") as mes_creacion')
        )
            ->join('odontologos', 'odontologos.clinica', '=', 'clinicas.id')
            ->join('ordenes', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->groupBy('clinicas.nombre', 'odontologos.nombres', 'odontologos.apellido_p', 'odontologos.apellido_m', 'odontologos.celular', 'mes_creacion')
            ->orderByDesc('ordenes_count')
            ->where('ordenes.estado', '=', 2)
            ->where('clinicas.id', '!=', 0)
            ->whereBetween(DB::raw('DATE(ordenes.created_at)'), [$primerDiaMesActual, $ultimoDiaMesActual])
            ->limit(25)
            ->get();

        $resultadosFormateados = $clinicas->map(function ($clinica) {
            $mesCreacion = \Carbon\Carbon::createFromFormat('Y-m', $clinica->mes_creacion)->format('Y-m');
            $clinica->mes_creacion = $mesCreacion;
            return $clinica;
        });

        return response()->json($resultadosFormateados);
    }

    public function obtenerClientesOdontologos()
    {
        $clinicas = clinicas::select(
            'clinicas.nombre',
            'odontologos.nombres as odontologo',
            'odontologos.apellido_p as apellido_p',
            'odontologos.apellido_m as apellido_m',
            'odontologos.celular as celular',
            DB::raw('COUNT(ordenes.id) as ordenes_count'),
            DB::raw('DATE_FORMAT(ordenes.created_at, "%Y-%m") as mes_creacion')
        )
            ->join('odontologos', 'odontologos.clinica', '=', 'clinicas.id')
            ->join('ordenes', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->groupBy('clinicas.nombre', 'odontologos.nombres', 'odontologos.apellido_p', 'odontologos.apellido_m', 'odontologos.celular', 'mes_creacion')
            ->orderByDesc('ordenes_count')
            ->where('ordenes.estado', '=', 2)
            ->where('clinicas.id', '!=', 0)
            ->get();
        $resultadosFormateados = $clinicas->map(function ($clinica) {
            $mesCreacion = \Carbon\Carbon::createFromFormat('Y-m', $clinica->mes_creacion)->format('Y-m');
            $clinica->mes_creacion = $mesCreacion;
            return $clinica;
        });

        return response()->json($resultadosFormateados);
    }

    public function obtenerClientesOdontologosFechas(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $fechaInicio .= ' 00:00:00';
        $fechaFin .= ' 23:59:59';

        $clinicas = clinicas::select(
            'clinicas.nombre',
            'odontologos.nombres as odontologo',
            'odontologos.apellido_p as apellido_p',
            'odontologos.apellido_m as apellido_m',
            'odontologos.celular as celular',
            DB::raw('COUNT(ordenes.id) as ordenes_count'),
            DB::raw('DATE_FORMAT(ordenes.created_at, "%Y-%m") as mes_creacion')
        )
            ->join('odontologos', 'odontologos.clinica', '=', 'clinicas.id')
            ->join('ordenes', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->groupBy('clinicas.nombre', 'odontologos.nombres', 'odontologos.apellido_p', 'odontologos.apellido_m', 'odontologos.celular', 'mes_creacion')
            ->orderByDesc('ordenes_count')
            ->where('ordenes.estado', '=', 2)
            ->where('clinicas.id', '!=', 0)
            ->whereBetween(DB::raw('DATE(ordenes.created_at)'), [$fechaInicio, $fechaFin])
            ->limit(25)
            ->get();

        // Procesar los resultados para cambiar el formato de mes_creacion
        $resultadosFormateados = $clinicas->map(function ($clinica) {
            $mesCreacion = \Carbon\Carbon::createFromFormat('Y-m', $clinica->mes_creacion)->format('Y-m');
            $clinica->mes_creacion = $mesCreacion;
            return $clinica;
        });

        return response()->json($resultadosFormateados);
    }


    public function obtenerClinicasConOrdenes()
    {
        $clinicas = clinicas::select(
            'clinicas.nombre as nombre',
            'clinicas.direccion as direccion',
            'clinicas.telefono as telefono',
            DB::raw('COUNT(ordenes.id) as ordenes_count'),
            DB::raw('DATE_FORMAT(ordenes.created_at, "%Y-%m") as mes_creacion')
        )
            ->join('ordenes', 'ordenes.id_clinica', '=', 'clinicas.id')
            ->groupBy('clinicas.nombre', 'clinicas.direccion', 'clinicas.telefono', 'mes_creacion')
            ->orderByDesc('ordenes_count')
            ->where('ordenes.estado', '=', 2)
            ->where('clinicas.id', '!=', 0)
            ->get();

        // Procesar los resultados para cambiar el formato de mes_creacion
        $resultadosFormateados = $clinicas->map(function ($clinica) {
            $mesCreacion = \Carbon\Carbon::createFromFormat('Y-m', $clinica->mes_creacion)->format('Y-m');
            $clinica->mes_creacion = $mesCreacion;
            return $clinica;
        });

        return response()->json($resultadosFormateados);
    }

    public function obtenerClinicasConOrdenesMes()
    {
        $primerDiaMesActual = Carbon::now()->startOfMonth()->startOfDay();
        $ultimoDiaMesActual = Carbon::now()->endOfMonth()->endOfDay();

        $clinicas = clinicas::select(
            'clinicas.nombre as nombre',
            'clinicas.direccion as direccion',
            'clinicas.telefono as telefono',
            DB::raw('COUNT(ordenes.id) as ordenes_count'),
            DB::raw('DATE_FORMAT(ordenes.created_at, "%Y-%m") as mes_creacion')
        )
            ->join('ordenes', 'ordenes.id_clinica', '=', 'clinicas.id')
            ->groupBy('clinicas.nombre', 'clinicas.direccion', 'clinicas.telefono', 'mes_creacion')
            ->orderByDesc('ordenes_count')
            ->where('ordenes.estado', '=', 2)
            ->whereBetween(DB::raw('DATE(ordenes.created_at)'), [$primerDiaMesActual, $ultimoDiaMesActual])
            ->where('clinicas.id', '!=', 0)
            ->limit(25)
            ->get();

        // Procesar los resultados para cambiar el formato de mes_creacion
        $resultadosFormateados = $clinicas->map(function ($clinica) {
            $mesCreacion = \Carbon\Carbon::createFromFormat('Y-m', $clinica->mes_creacion)->format('Y-m');
            $clinica->mes_creacion = $mesCreacion;
            return $clinica;
        });

        return response()->json($resultadosFormateados);
    }

    public function obtenerClinicasConOrdenesFECHAS(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $fechaInicio .= ' 00:00:00';
        $fechaFin .= ' 23:59:59';

        $clinicas = clinicas::select(
            'clinicas.nombre as nombre',
            'clinicas.direccion as direccion',
            'clinicas.telefono as telefono',
            DB::raw('COUNT(ordenes.id) as ordenes_count'),
            DB::raw('DATE_FORMAT(ordenes.created_at, "%Y-%m") as mes_creacion')
        )
            ->join('ordenes', 'ordenes.id_clinica', '=', 'clinicas.id')
            ->groupBy('clinicas.nombre', 'clinicas.direccion', 'clinicas.telefono', 'mes_creacion')
            ->orderByDesc('ordenes_count')
            ->where('ordenes.estado', '=', 2)
            ->whereBetween(DB::raw('DATE(ordenes.created_at)'), [$fechaInicio, $fechaFin])
            ->where('clinicas.id', '!=', 0)
            ->limit(25)
            ->get();

        // Procesar los resultados para cambiar el formato de mes_creacion
        $resultadosFormateados = $clinicas->map(function ($clinica) {
            $mesCreacion = \Carbon\Carbon::createFromFormat('Y-m', $clinica->mes_creacion)->format('Y-m');
            $clinica->mes_creacion = $mesCreacion;
            return $clinica;
        });

        return response()->json($resultadosFormateados);
    }

    public function reproteIngresos()
    {
        $clinicas = ordenes::select('ordenes.*')
            ->orderBy('ordenes.id', 'desc')
            ->where('ordenes.estado', '=', 2)
            ->get();
        return response()->json($clinicas);
    }

    public function reproteIngresosMes()
    {
        $primerDiaMesActual = Carbon::now()->startOfMonth()->startOfDay();
        $ultimoDiaMesActual = Carbon::now()->endOfMonth()->endOfDay();

        $clinicas = ordenes::select('ordenes.*')
            ->orderBy('ordenes.id', 'desc')
            ->where('ordenes.estado', '=', 2)
            ->whereBetween(DB::raw('DATE(ordenes.created_at)'), [$primerDiaMesActual, $ultimoDiaMesActual])
            ->get();
        return response()->json($clinicas);
    }

    public function reporteComisionesFechas(Request $request)
    {

        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

        $fechaInicio .= ' 00:00:00';
        $fechaFin .= ' 23:59:59';

        $odontologos = odontologos::select('odontologos.nombres', 'odontologos.apellido_p', 'odontologos.apellido_m', 'ordenes.id')
            ->join('ordenes', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->where('ordenes.estado', "!=", 0)
            ->where('odontologos.id', "!=", 0)
            ->whereBetween('ordenes.created_at', [$fechaInicio, $fechaFin])
            ->orderBy('ordenes.id', 'desc')
            ->get();

        $grupos = [];
        foreach ($odontologos as $odontologo) {
            $nombreCompleto = $odontologo->nombres . ' ' . $odontologo->apellido_p . ' ' . $odontologo->apellido_m;
            if (!isset($grupos[$nombreCompleto])) {
                $grupos[$nombreCompleto] = [
                    'odontologo' => $nombreCompleto,
                    'idsOrdenes' => [],
                ];
            }
            $grupos[$nombreCompleto]['idsOrdenes'][] = $odontologo->id;
        }

        $resultados = array_values($grupos);

        return response()->json($resultados);
    }

    public function indexReporte(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $fechaInicio .= ' 00:00:00';
        $fechaFin .= ' 23:59:59';

        $ordenes = ordenes::join('pacientes', 'ordenes.id_paciente', '=', 'pacientes.id')
            ->join('odontologos', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->select(
                'ordenes.*',
                'pacientes.nombres as paciente',
                'pacientes.apellido_p as paciente_apellido_p',
                'pacientes.apellido_m as paciente_apellido_m',
                'pacientes.tipo_documento_paciente_odontologo as TipoDocumentoPaciente',
                'pacientes.numero_documento_paciente_odontologo  as documentoPaciente',
                'odontologos.nombres as odontologo',
                'odontologos.apellido_p as odontologo_apellido_p',
                'odontologos.apellido_m as odontologo_apellido_m',
                'odontologos.cop as copOdontologo'
            )
            ->orderBy('ordenes.id', 'desc')
            ->whereBetween('ordenes.created_at', [$fechaInicio, $fechaFin])
            ->get();
        return $ordenes;
    }

    public function reporteComisiones()
    {
        $odontologos = odontologos::select('odontologos.nombres', 'odontologos.apellido_p', 'odontologos.apellido_m', 'ordenes.id')
            ->join('ordenes', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->where('ordenes.estado', "!=", 0)
            ->where('odontologos.id', "!=", 0)
            ->orderBy('ordenes.id', 'desc')
            ->get();

        $grupos = [];
        foreach ($odontologos as $odontologo) {
            $nombreCompleto = $odontologo->nombres . ' ' . $odontologo->apellido_p . ' ' . $odontologo->apellido_m;
            if (!isset($grupos[$nombreCompleto])) {
                $grupos[$nombreCompleto] = [
                    'odontologo' => $nombreCompleto,
                    'idsOrdenes' => [],
                ];
            }
            $grupos[$nombreCompleto]['idsOrdenes'][] = $odontologo->id;
        }

        $resultados = array_values($grupos);

        return response()->json($resultados);
    }

    public function reporteComisionesMes()
    {
        $primerDiaMesActual = Carbon::now()->startOfMonth()->startOfDay();
        $ultimoDiaMesActual = Carbon::now()->endOfMonth()->endOfDay();

        $odontologos = odontologos::select('odontologos.nombres', 'odontologos.apellido_p', 'odontologos.apellido_m', 'ordenes.id')
            ->join('ordenes', 'ordenes.id_odontologo', '=', 'odontologos.id')
            ->where('ordenes.estado', "!=", 0)
            ->where('odontologos.id', "!=", 0)
            ->whereBetween(DB::raw('DATE(ordenes.created_at)'), [$primerDiaMesActual, $ultimoDiaMesActual])
            ->orderBy('ordenes.id', 'desc')
            ->get();

        $grupos = [];
        foreach ($odontologos as $odontologo) {
            $nombreCompleto = $odontologo->nombres . ' ' . $odontologo->apellido_p . ' ' . $odontologo->apellido_m;
            if (!isset($grupos[$nombreCompleto])) {
                $grupos[$nombreCompleto] = [
                    'odontologo' => $nombreCompleto,
                    'idsOrdenes' => [],
                ];
            }
            $grupos[$nombreCompleto]['idsOrdenes'][] = $odontologo->id;
        }

        $resultados = array_values($grupos);

        return response()->json($resultados);
    }

    public function destroy($id)
    {
        $result = ordenes::destroy($id);
        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }
}

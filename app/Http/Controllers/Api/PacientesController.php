<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\pacientes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PacientesController extends Controller
{
    public function index()
    {
        $pacientes = pacientes::orderBy('pacientes.id', 'desc')->get();
        return $pacientes;
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
    
        $resultados = pacientes::where(function ($query) use ($search) {
            $query->whereRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellido_p, ' ', pacientes.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ?", [$search]);
    
            $nombres = explode(' ', $search);
            if (count($nombres) >= 2) {
                $nombre = '%' . $this->quitarAcentos($nombres[0]) . '%';
                $apellido = '%' . $this->quitarAcentos($nombres[1]) . '%';
    
                $query->orWhereRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellido_p) COLLATE utf8mb4_unicode_ci LIKE ? AND CONCAT(pacientes.nombres, ' ', pacientes.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ?", [$nombre, $apellido]);
                $query->orWhereRaw("CONCAT(pacientes.nombres, ' ', pacientes.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ? AND CONCAT(pacientes.nombres, ' ', pacientes.apellido_p) COLLATE utf8mb4_unicode_ci LIKE ?", [$nombre, $apellido]);
            }
        })
        ->orWhere('id', 'LIKE', $search)
        ->orWhere('nombres', 'LIKE', $search)
        ->orWhere('apellido_m', 'LIKE', $search)
        ->orWhere('apellido_p', 'LIKE', $search)
        ->orWhere('correo', 'LIKE', $search)
        ->orWhere('numero_documento_paciente_odontologo', 'LIKE', $search)
        ->orWhere('celular', 'LIKE', $search)
        ->orderBy('pacientes.id', 'desc')
        ->get();
    
        return response()->json($resultados);
    }
    public function store(Request $request)
    {
        $savePaciente = new pacientes();

        $validatedData = $request->validate([
            'id_rol' => 'required|numeric',
            'nombres' => 'required|regex:/^[^0-9]+$/',
            'apellido_p' => 'required|regex:/^[^0-9]+$/',
            'apellido_m' => 'required|regex:/^[^0-9]+$/',
            'f_nacimiento' => 'nullable|string',
            'nombre_apoderado' => 'nullable|regex:/^[^0-9]+$/',
            'tipo_documento_apoderado' => 'nullable|integer',
            'documento_apoderado' => 'string|nullable',
            'tipo_documento_paciente_odontologo' => 'required|numeric',
            'numero_documento_paciente_odontologo' => 'required|string',
            'celular' => 'required|numeric|digits:9',
            'correo' => 'required|string',
            'genero' => 'required|numeric',
            'embarazada' => 'numeric|nullable',
            'enfermedades' => 'string|nullable',
            'discapacidades' => 'string|nullable',
            'paciente_especial' => 'string|nullable',
        ]);


        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $request->validate([
            'tipo_documento_paciente_odontologo' => 'required',
            'numero_documento_paciente_odontologo' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = DB::table('pacientes')
                        ->where('numero_documento_paciente_odontologo', $value)
                        ->where(function ($query) use ($request) {
                            $query->where('tipo_documento_paciente_odontologo', $request->tipo_documento_paciente_odontologo);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('El tipo de documento y numero de documento ya estan registrados para otro cliente');
                    }
                },
            ],
        ]);


        $savePaciente->id_rol = $validatedData['id_rol'];
        $savePaciente->nombres = $validatedData['nombres'];
        $savePaciente->apellido_p = $validatedData['apellido_p'];
        $savePaciente->apellido_m = $validatedData['apellido_m'];
        $savePaciente->f_nacimiento = $validatedData['f_nacimiento'];
        $savePaciente->nombre_apoderado = $validatedData['nombre_apoderado'];
        $savePaciente->tipo_documento_apoderado = $validatedData['tipo_documento_apoderado'];
        $savePaciente->documento_apoderado = $validatedData['documento_apoderado'];
        $savePaciente->tipo_documento_paciente_odontologo = $validatedData['tipo_documento_paciente_odontologo'];
        $savePaciente->numero_documento_paciente_odontologo = $validatedData['numero_documento_paciente_odontologo'];
        $savePaciente->celular = $validatedData['celular'];
        $savePaciente->correo = $validatedData['correo'];
        $savePaciente->genero = $validatedData['genero'];
        $savePaciente->embarazada = $validatedData['embarazada'];
        $savePaciente->enfermedades = $validatedData['enfermedades'];
        $savePaciente->discapacidades = $validatedData['discapacidades'];
        $savePaciente->paciente_especial = $validatedData['paciente_especial'];

        $result = $savePaciente->save();

        if ($result) {
            return response()->json(['status' => "success", 'paciente' => $savePaciente]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function store2(Request $request)
    {
        $savePaciente = new pacientes();

        $validatedData = $request->validate([
            'id_rol' => 'required|numeric',
            'nombres' => 'required|regex:/^[^0-9]+$/',
            'apellido_p' => 'required|regex:/^[^0-9]+$/',
            'apellido_m' => 'required|regex:/^[^0-9]+$/',
            'f_nacimiento' => 'nullable|string',
            'nombre_apoderado' => 'nullable|regex:/^[^0-9]+$/',
            'tipo_documento_apoderado' => 'nullable|integer',
            'documento_apoderado' => 'string|nullable',
            'tipo_documento_paciente_odontologo' => 'required|numeric',
            'numero_documento_paciente_odontologo' => 'required|string',
            'celular' => 'required|numeric|min:100000000 |max:999999999',
            'correo' => 'required|string',
            'genero' => 'required|numeric',
            'embarazada' => 'numeric|nullable',
            'enfermedades' => 'string|nullable',
            'discapacidades' => 'string|nullable',
            'paciente_especial' => 'string|nullable',
        ]);


        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $request->validate([
            'tipo_documento_paciente_odontologo' => 'required',
            'numero_documento_paciente_odontologo' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = DB::table('pacientes')
                        ->where('numero_documento_paciente_odontologo', $value)
                        ->where(function ($query) use ($request) {
                            $query->where('tipo_documento_paciente_odontologo', $request->tipo_documento_paciente_odontologo);
                        })
                        ->exists();

                    if ($exists) {
                        $fail('El tipo de documento y numero de documento ya estan registrados para otro cliente');
                    }
                },
            ],
        ]);



        $savePaciente->id_rol = $validatedData['id_rol'];
        $savePaciente->nombres = $validatedData['nombres'];
        $savePaciente->apellido_p = $validatedData['apellido_p'];
        $savePaciente->apellido_m = $validatedData['apellido_m'];
        $savePaciente->f_nacimiento = $validatedData['f_nacimiento'];
        $savePaciente->nombre_apoderado = $validatedData['nombre_apoderado'];
        $savePaciente->tipo_documento_apoderado = $validatedData['tipo_documento_apoderado'];
        $savePaciente->documento_apoderado = $validatedData['documento_apoderado'];
        $savePaciente->tipo_documento_paciente_odontologo = $validatedData['tipo_documento_paciente_odontologo'];
        $savePaciente->numero_documento_paciente_odontologo = $validatedData['numero_documento_paciente_odontologo'];
        $savePaciente->celular = $validatedData['celular'];
        $savePaciente->correo = $validatedData['correo'];
        $savePaciente->genero = $validatedData['genero'];
        $savePaciente->embarazada = $validatedData['embarazada'];
        $savePaciente->enfermedades = $validatedData['enfermedades'];
        $savePaciente->discapacidades = $validatedData['discapacidades'];
        $savePaciente->paciente_especial = $validatedData['paciente_especial'];

        $result = $savePaciente->save();

        if ($result) {
            return response()->json(['status' => "success", 'paciente' => $savePaciente]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function eyes(Request $request)
    {
        $request->validate([
            "tipo_documento_paciente_odontologo" => "required|numeric",
            "numero_documento_paciente_odontologo" => "required|string"
        ]);

        $paciente = pacientes::where("numero_documento_paciente_odontologo", $request->numero_documento_paciente_odontologo)
            ->where("tipo_documento_paciente_odontologo", $request->tipo_documento_paciente_odontologo)
            ->first();

        if ($paciente) {
            //CREAMOS EL TOKEN
            return response()->json([
                "status" => "success",
                "message" => "Paciente encontrado",
                "paciente" => $paciente
            ]);
        } else {
            return response()->json([
                "status" => "error",
                "message" => "El paciente no existe"
            ]);
        }
    }

    public function show($id)
    {
        $verPaciente = pacientes::find($id);
        return $verPaciente;
    }

    public function update(Request $request, $id)
    {
        $updatePaciente = pacientes::findOrFail($id);

        $validatedData = $request->validate([
            'id_rol' => 'required|numeric',
            'nombres' => 'required|regex:/^[^0-9]+$/',
            'apellido_p' => 'required|regex:/^[^0-9]+$/',
            'apellido_m' => 'required|regex:/^[^0-9]+$/',
            'f_nacimiento' => 'nullable|string',
            'nombre_apoderado' => 'nullable|regex:/^[^0-9]+$/',
            'tipo_documento_apoderado' => 'nullable|integer',
            'documento_apoderado' => 'string|nullable',
            'tipo_documento_paciente_odontologo' => 'required|numeric',
            'numero_documento_paciente_odontologo' => 'required|string',
            'celular' => 'required|numeric|min:100000000 |max:999999999',
            'correo' => 'required|string',
            'genero' => 'required|numeric',
            'embarazada' => 'numeric|nullable',
            'enfermedades' => 'string|nullable',
            'discapacidades' => 'string|nullable',
            'paciente_especial' => 'string|nullable',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $request->validate([
            'tipo_documento_paciente_odontologo' => 'required',
            'numero_documento_paciente_odontologo' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = DB::table('pacientes')
                        ->where('numero_documento_paciente_odontologo', $value)
                        ->where(function ($query) use ($request) {
                            $query->where('tipo_documento_paciente_odontologo', $request->tipo_documento_paciente_odontologo);
                        })
                        ->where('id', '!=', $request->id)
                        ->exists();

                    if ($exists) {
                        $fail('El tipo de documento y numero de documento ya estan registrados para otro cliente');
                    }
                },
            ],
        ]);


        $updatePaciente->id_rol = $validatedData['id_rol'];
        $updatePaciente->nombres = $validatedData['nombres'];
        $updatePaciente->apellido_p = $validatedData['apellido_p'];
        $updatePaciente->apellido_m = $validatedData['apellido_m'];
        $updatePaciente->f_nacimiento = $validatedData['f_nacimiento'];
        $updatePaciente->nombre_apoderado = $validatedData['nombre_apoderado'];
        $updatePaciente->tipo_documento_apoderado = $validatedData['tipo_documento_apoderado'];
        $updatePaciente->documento_apoderado = $validatedData['documento_apoderado'];
        $updatePaciente->tipo_documento_paciente_odontologo = $validatedData['tipo_documento_paciente_odontologo'];
        $updatePaciente->numero_documento_paciente_odontologo = $validatedData['numero_documento_paciente_odontologo'];
        $updatePaciente->celular = $validatedData['celular'];
        $updatePaciente->correo = $validatedData['correo'];
        $updatePaciente->genero = $validatedData['genero'];
        $updatePaciente->embarazada = $validatedData['embarazada'];
        $updatePaciente->enfermedades = $validatedData['enfermedades'];
        $updatePaciente->discapacidades = $validatedData['discapacidades'];
        $updatePaciente->paciente_especial = $validatedData['paciente_especial'];


        $result = $updatePaciente->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function destroy($id)
    {
        $result = pacientes::destroy($id);
        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }
}

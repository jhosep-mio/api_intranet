<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinica;
use App\Models\odontologos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OdontologosController extends Controller
{
    public function index()
    {
        $odontologos = odontologos::orderBy('odontologos.id', 'desc')
            ->get();
        return $odontologos;
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

        $resultados = odontologos::where(function ($query) use ($search) {
            $query->whereRaw("CONCAT(odontologos.nombres, ' ', odontologos.apellido_p, ' ', odontologos.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ?", [$search]);

            $nombres = explode(' ', $search);
            if (count($nombres) >= 2) {
                $nombre = '%' . $this->quitarAcentos($nombres[0]) . '%';
                $apellido = '%' . $this->quitarAcentos($nombres[1]) . '%';

                $query->orWhereRaw("CONCAT(odontologos.nombres, ' ', odontologos.apellido_p) COLLATE utf8mb4_unicode_ci LIKE ? AND CONCAT(odontologos.nombres, ' ', odontologos.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ?", [$nombre, $apellido]);
                $query->orWhereRaw("CONCAT(odontologos.nombres, ' ', odontologos.apellido_m) COLLATE utf8mb4_unicode_ci LIKE ? AND CONCAT(odontologos.nombres, ' ', odontologos.apellido_p) COLLATE utf8mb4_unicode_ci LIKE ?", [$nombre, $apellido]);
            }
        })
            ->orWhere('id', 'LIKE', $search)
            ->orWhere('nombres', 'LIKE', $search)
            ->orWhere('apellido_m', 'LIKE', $search)
            ->orWhere('apellido_p', 'LIKE', $search)
            ->orWhere('clinica', 'LIKE', $search)
            ->orWhere('correo', 'LIKE', $search)
            ->orWhere('numero_documento_paciente_odontologo', 'LIKE', $search)
            ->orWhere('celular', 'LIKE', $search)
            ->orWhere('cop', 'LIKE', $search)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($resultados);
    }

    public function store(Request $request)
    {
        $saveOdontologos = new odontologos();

        $validatedData = $request->validate([
            'id_rol' => 'required|numeric',
            'clinica' => 'numeric',
            'cop' => 'required|numeric|max:9999999999',
            'c_bancaria' => 'string|nullable',
            'cci' => 'nullable|string ',
            'nombre_banco' => 'string|nullable',
            'nombres' => 'required|regex:/^[^0-9]+$/',
            'apellido_p' => 'required|regex:/^[^0-9]+$/',
            'apellido_m' => 'required|regex:/^[^0-9]+$/',
            'f_nacimiento' => 'nullable|string',
            'tipo_documento_paciente_odontologo' => 'required|numeric',
            'numero_documento_paciente_odontologo' => 'nullable|string',
            'celular' => 'nullable|numeric|min:100000000|max:999999999',
            'correo' => 'nullable|string',
            'genero' => 'required|numeric',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $request->validate([
            'tipo_documento_paciente_odontologo' => 'required',
            'numero_documento_paciente_odontologo' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $exists = DB::table('odontologos')
                            ->where('numero_documento_paciente_odontologo', $value)
                            ->where(function ($query) use ($request) {
                                $query->where('tipo_documento_paciente_odontologo', $request->tipo_documento_paciente_odontologo);
                            })
                            ->exists();
                        if ($exists) {
                            $fail('El tipo de documento y numero de documento ya estan registrados para otro cliente');
                        }
                    }
                },
            ],
        ]);

        $saveOdontologos->id_rol = $validatedData['id_rol'];
        $saveOdontologos->clinica = $validatedData['clinica'];
        $saveOdontologos->cop = $validatedData['cop'];
        $saveOdontologos->c_bancaria = $validatedData['c_bancaria'];
        $saveOdontologos->cci = $validatedData['cci'];
        $saveOdontologos->nombre_banco = $validatedData['nombre_banco'];
        $saveOdontologos->nombres = $validatedData['nombres'];
        $saveOdontologos->apellido_p = $validatedData['apellido_p'];
        $saveOdontologos->apellido_m = $validatedData['apellido_m'];
        $saveOdontologos->f_nacimiento = $validatedData['f_nacimiento'];
        $saveOdontologos->tipo_documento_paciente_odontologo = $validatedData['tipo_documento_paciente_odontologo'];
        $saveOdontologos->numero_documento_paciente_odontologo = $validatedData['numero_documento_paciente_odontologo'];
        $saveOdontologos->celular = $validatedData['celular'];
        $saveOdontologos->correo = $validatedData['correo'];
        $saveOdontologos->genero = $validatedData['genero'];

        $result = $saveOdontologos->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function store3(Request $request)
    {
        $saveOdontologos = new odontologos();

        $validatedData = $request->validate([
            'id_rol' => 'required|numeric',
            'clinica' => 'numeric',
            'cop' => 'required|numeric|max:9999999999',
            'c_bancaria' => 'string|nullable',
            'cci' => 'nullable|string ',
            'nombre_banco' => 'string|nullable',
            'nombres' => 'required|regex:/^[^0-9]+$/',
            'apellido_p' => 'required|regex:/^[^0-9]+$/',
            'apellido_m' => 'required|regex:/^[^0-9]+$/',
            'f_nacimiento' => 'nullable|string',
            'tipo_documento_paciente_odontologo' => 'required|numeric',
            'numero_documento_paciente_odontologo' => 'nullable|string',
            'celular' => 'nullable|numeric|min:100000000|max:999999999',
            'correo' => 'nullable|string',
            'genero' => 'required|numeric',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $request->validate([
            'tipo_documento_paciente_odontologo' => 'required',
            'numero_documento_paciente_odontologo' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $exists = DB::table('odontologos')
                            ->where('numero_documento_paciente_odontologo', $value)
                            ->where(function ($query) use ($request) {
                                $query->where('tipo_documento_paciente_odontologo', $request->tipo_documento_paciente_odontologo);
                            })
                            ->exists();
                        if ($exists) {
                            $fail('El tipo de documento y numero de documento ya estan registrados para otro cliente');
                        }
                    }
                },
            ],
        ]);

        $saveOdontologos->id_rol = $validatedData['id_rol'];
        $saveOdontologos->clinica = $validatedData['clinica'];
        $saveOdontologos->cop = $validatedData['cop'];
        $saveOdontologos->c_bancaria = $validatedData['c_bancaria'];
        $saveOdontologos->cci = $validatedData['cci'];
        $saveOdontologos->nombre_banco = $validatedData['nombre_banco'];
        $saveOdontologos->nombres = $validatedData['nombres'];
        $saveOdontologos->apellido_p = $validatedData['apellido_p'];
        $saveOdontologos->apellido_m = $validatedData['apellido_m'];
        $saveOdontologos->f_nacimiento = $validatedData['f_nacimiento'];
        $saveOdontologos->tipo_documento_paciente_odontologo = $validatedData['tipo_documento_paciente_odontologo'];
        $saveOdontologos->numero_documento_paciente_odontologo = $validatedData['numero_documento_paciente_odontologo'];
        $saveOdontologos->celular = $validatedData['celular'];
        $saveOdontologos->correo = $validatedData['correo'];
        $saveOdontologos->genero = $validatedData['genero'];

        $result = $saveOdontologos->save();

        if ($result) {
            return response()->json(['status' => "success", 'odontologo' => $saveOdontologos]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function store2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_rol' => 'required|numeric',
            'clinica' => 'numeric',
            'cop' => 'required|numeric|max:9999999999',
            'c_bancaria' => 'string|nullable',
            'cci' => 'nullable|string ',
            'nombre_banco' => 'string|nullable',
            'nombres' => 'required|regex:/^[^0-9]+$/',
            'apellido_p' => 'required|regex:/^[^0-9]+$/',
            'apellido_m' => 'required|regex:/^[^0-9]+$/',
            'f_nacimiento' => 'required|string',
            'tipo_documento_paciente_odontologo' => 'required|numeric',
            'numero_documento_paciente_odontologo' => 'nullable|string',
            'celular' => 'required|numeric|min:100000000 |max:999999999',
            'correo' => 'required|string',
            'genero' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['error' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $request->validate([
            'tipo_documento_paciente_odontologo' => 'required',
            'numero_documento_paciente_odontologo' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $exists = DB::table('odontologos')
                            ->where('numero_documento_paciente_odontologo', $value)
                            ->where(function ($query) use ($request) {
                                $query->where('tipo_documento_paciente_odontologo', $request->tipo_documento_paciente_odontologo);
                            })
                            ->exists();
                        if ($exists) {
                            $fail('El tipo de documento y numero de documento ya estan registrados para otro cliente');
                        }
                    }
                },
            ],
        ]);

        // Obtiene el número de documento del paciente
        $numeroDocumento = $request->input('numero_documento_paciente_odontologo');
        $celular = $request->input('celular');
        $correo = $request->input('correo');
        $cop = $request->input('cop');

        // Verifica si el DNI ya existe en la base de datos
        // $existeDni = odontologos::where('numero_documento_paciente_odontologo', $numeroDocumento)->exists();

        $existeCop = odontologos::where('cop', $cop)->exists();
        $existeCelular = odontologos::where('celular', $celular)->exists();
        $esiteCorreo = odontologos::where('correo', $correo)->exists();

        if ($existeCelular) {
            return response()->json(['status' => "celular_ya_registro"]);
        } else if ($esiteCorreo) {
            return response()->json(['status' => "correo_ya_registrado"]);
        } else if ($existeCop) {
            return response()->json(['status' => "cop_ya_registrado"]);
        }

        return response()->json(['status' => "success"]);
    }

    public function show($id)
    {
        $verOdontologo = odontologos::find($id);
        return $verOdontologo;
    }

    public function update(Request $request, $id)
    {
        $updateOdontologo = odontologos::findOrFail($id);

        $validatedData = $request->validate([
            'id_rol' => 'required|numeric',
            'clinica' => 'numeric',
            'cop' => 'required|numeric|max:9999999999',
            'c_bancaria' => 'string|nullable',
            'cci' => 'nullable|string ',
            'nombre_banco' => 'string|nullable',
            'nombres' => 'required|regex:/^[^0-9]+$/',
            'apellido_p' => 'required|regex:/^[^0-9]+$/',
            'apellido_m' => 'required|regex:/^[^0-9]+$/',
            'f_nacimiento' => 'nullable|string',
            'tipo_documento_paciente_odontologo' => 'required|numeric',
            'numero_documento_paciente_odontologo' => 'nullable|string',
            'celular' => ['sometimes', 'nullable', 'numeric', 'min:100000000', 'max:999999999'],
            'correo' => 'nullable|string',
            'genero' => 'required|numeric',
        ]);

        $validatedData = array_map(function ($value) {
            return $value === 'null' ? null : $value;
        }, $validatedData);

        $request->validate([
            'tipo_documento_paciente_odontologo' => 'required',
            'numero_documento_paciente_odontologo' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value) {
                        $exists = DB::table('odontologos')
                            ->where('numero_documento_paciente_odontologo', $value)
                            ->where(function ($query) use ($request) {
                                $query->where('tipo_documento_paciente_odontologo', $request->tipo_documento_paciente_odontologo);
                            })
                            ->where('id', '!=', $request->id)
                            ->exists();
                        if ($exists) {
                            $fail('El tipo de documento y numero de documento ya estan registrados para otro cliente');
                        }
                    }
                },
            ],
        ]);

        $updateOdontologo->id_rol = $validatedData['id_rol'];
        $updateOdontologo->clinica = $validatedData['clinica'];
        $updateOdontologo->cop = $validatedData['cop'];
        $updateOdontologo->c_bancaria = $validatedData['c_bancaria'];
        $updateOdontologo->cci = $validatedData['cci'];
        $updateOdontologo->nombre_banco = $validatedData['nombre_banco'];
        $updateOdontologo->nombres = $validatedData['nombres'];
        $updateOdontologo->apellido_p = $validatedData['apellido_p'];
        $updateOdontologo->apellido_m = $validatedData['apellido_m'];
        $updateOdontologo->f_nacimiento = $validatedData['f_nacimiento'];
        $updateOdontologo->tipo_documento_paciente_odontologo = $validatedData['tipo_documento_paciente_odontologo'];
        $updateOdontologo->numero_documento_paciente_odontologo = $validatedData['numero_documento_paciente_odontologo'];
        $updateOdontologo->celular = $validatedData['celular'];
        $updateOdontologo->correo = $validatedData['correo'];
        $updateOdontologo->genero = $validatedData['genero'];

        $result = $updateOdontologo->save();

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function destroy($id)
    {
        $result = odontologos::destroy($id);
        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }
}

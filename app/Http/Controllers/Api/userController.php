<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\odontologos;
use App\Models\pacientes;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;


class userController extends Controller
{
    public function index(){
        $registros = User::orderBy('users.id', 'desc')
                    ->get();
        return $registros;
    }

    public function show($id){
        $verproducto = User::find($id);
        return $verproducto;
    }

    public function update(Request $request, $id){
        $updateClinica= User::findOrFail($id);

       $request->validate([
            'name' => 'required',
            'id_rol' => 'required',
        ]);

        $updateClinica->name = $request->name;
        $updateClinica->id_rol = $request->id_rol;
        $updateClinica->email = $request->email;

        if ($request->password) {
            $updateClinica->password = Hash::make($request->password);
        }
        $result =$updateClinica->save();
        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }
    // public function update(Request $request, $id){
    //     $request->validate([
    //         'name' => 'required',
    //         'id_rol' => 'required',
    //         'email' => 'required|email|unique:users',
    //     ]);
    //     $user = User::find($id);
    //     // Actualiza los campos del usuario
    //     $user->name = $request->name;
    //     $user->id_rol = $request->id_rol;
    //     $user->email = $request->email;
    //     // Actualiza la contraseña solo si se proporciona una nueva contraseña
    //     if ($request->password) {
    //         $user->password = Hash::make($request->password);
    //     }
    //     $user->save();

    //     if($user){
    //         return response()->json([
    //             "status" => "success",
    //             "message" => "Actualizado Correctamente"
    //         ]);
    //     }else{
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Error"
    //         ]);
    //     }
    // }

    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'id_rol' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->id_rol = $request->id_rol;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();

        if($user){
            return response()->json([
                "status" => "success",
                "message" => "Registrado Correctamente"
            ]);
        }else{
            return response()->json([
                "status" => "error",
                "message" => "Error"
            ]);
        }
    }
    public function login(Request $request) {
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        $user = User::where("email", "=", $request->email)->first();

        if(isset($user->id)){
            if(Hash::check($request->password, $user->password)){
                //CREAMOS EL TOKEN
               $token = $user->createToken("auth_token")->plainTextToken;
               if($user->id_rol == 99){
                   return response()->json([
                        "status" => "success",
                        "message" => "Usuario logueado exitosamente como administrador",
                        "acces_token" => $token,
                        "user" => $user 
                    ]);
               }else if($user->id_rol == 98){
                    return response()->json([
                        "status" => "success",
                        "message" => "Usuario logueado exitosamente como recepcionista",
                        "acces_token" => $token,
                        "user" => $user 
                    ]);
               }
                //SI TODO ESTA CORRECTO
            }else{
                return response()->json([
                    "status" => "invalid",
                    "message" => "La contraseña es incorrecta"
                ]);
            }
        }else{
            return response()->json([
                "status" => "error",
                "message" => "El usuario no existe"
            ]);
        }
    }   

    public function loginClientes(Request $request) {
        $request->validate([
            "user" => "required",
            "password" => "required"
        ]);

        $user = pacientes::where("numero_documento_paciente_odontologo", "=", $request->user)->first();

        $odontologo = odontologos::where("correo", "=", $request->user)->first();

        if(isset($odontologo) ){
            if(($request->password == $odontologo->cop)){
                //CREAMOS EL TOKEN
               $token = $odontologo->createToken("auth_token")->plainTextToken;
                return response()->json([
                    "status" => "success",
                    "message" => "Usuario logueado exitosamente como doctor",
                    "acces_token" => $token,
                    "user" => $odontologo 
                ]);
                //SI TODO ESTA CORRECTO
            }else{
                return response()->json([
                    "status" => "invalid",
                    "message" => "La contraseña es incorrecta"
                ]);
            }
        }else if(isset($user->id)){
            if(($request->password == $user->numero_documento_paciente_odontologo)){
                //CREAMOS EL TOKEN
               $token = $user->createToken("auth_token")->plainTextToken;
                return response()->json([
                    "status" => "success",
                    "message" => "Usuario logueado exitosamente como paciente",
                    "acces_token" => $token,
                    "user" => $user 
                ]);
                //SI TODO ESTA CORRECTO
            }else{
                return response()->json([
                    "status" => "invalid",
                    "message" => "La contraseña es incorrecta"
                ]);
            }
        }else{
            return response()->json([
                "status" => "error",
                "message" => "El usuario no existe"
            ]);
        }
    }   

    public function userProfile(){
        return response()->json([
            "status" => "success",
            "message" => "Perfil del usuario",
            "user" => auth()->user()
        ]);
    }
    
    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return response()->json([
            "status" => "success",
            "message" => "Se cerró la sesión exitosamente",
        ]);
    }

    public function destroy($id){
        if($id == 1){
            return response()->json(['status'=>"error"]);
        }
        $result = User::destroy($id);
        if($result){
            return response()->json(['status'=>"success"]);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NotificacionFinal;
use App\Mail\NotificacionRecuperar;
use App\Mail\NotificationPacientes;
use App\Models\odontologos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class mailPacientesController extends Controller
{
    public function enviarCorreo(Request $request){
        
        $codigo = mt_rand(100000, 999999); // Generar código aleatorio
        $email = $request->input('correo');

        $registro = DB::table('password_resets')
        ->where('email', $email)
        ->first();

        if ($registro) {
        // El registro ya existe, actualízalo con el nuevo código.
        DB::table('password_resets')
        ->where('email', $email)
        ->update(['token' => $codigo, 'created_at' => now()]);
        }else{
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $codigo,
                'created_at' => now(),
            ]);
        }
        
        $correo_odontologo = new NotificationPacientes($codigo); 
        Mail::to($email)->send($correo_odontologo);
        // Devuelve una respuesta adecuada a React
        return response()->json(['status'=>"success"], 200);
    }

    public function validarCodigo(Request $request){
        $codigo = $request->input('codigo');
        $email = $request->input('correo');

        $registro = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $codigo)
            ->first();
        if ($registro) {
            // Código válido, realiza la lógica adicional que necesites.
            // Puedes marcar el correo electrónico como verificado en tu base de datos, permitir el acceso al usuario, etc.
            DB::table('password_resets')
            ->where('email', '=', $email)
            ->delete();
            return response()->json(['status'=>"success"], 200);
        } else {
            // Código inválido, muestra un mensaje de error al usuario.
            return response()->json(['error' => 'Código inválido'], 422);
        }
    }   

    public function enviarCorreoFinal(Request $request){
        $email = $request->input('correo');
        $cop = $request->input('cop');
        $numero_documento_paciente_odontologo = $request->input('numero_documento_paciente_odontologo');
        
        $correo_odontologo = new NotificacionFinal($email,$cop); 
        Mail::to($email)->send($correo_odontologo);
        // Devuelve una respuesta adecuada a React
        return response()->json(['status'=>"success"], 200);
    }

    public function recuperarCuenta(Request $request){
        $email = $request->input('correo');
        $tipo = $request->input('tipo');

        if ($tipo == 1) {
            $registro = DB::table('odontologos')
                ->where('correo', $email)
                ->first();
            if ($registro) {
                $cop = $registro->correo;
                $numero_documento_paciente_odontologo = $registro->cop;
            } else {
                $cop = null;
                $numero_documento_paciente_odontologo = null;
            }
        } else if ($tipo == 0) {
            $registro = DB::table('pacientes')
                ->where('correo', $email)
                ->first();
        
            if ($registro) {
                $cop = $registro->numero_documento_paciente_odontologo;
                $numero_documento_paciente_odontologo = $registro->numero_documento_paciente_odontologo;
            } else {
                $cop = null;
                $numero_documento_paciente_odontologo = null;
            }
        } else {
            return response()->json(['status' => "no_exise"]);
        }
        
        if($cop != null && $numero_documento_paciente_odontologo != null){
            $correo_odontologo = new NotificacionRecuperar($cop,$numero_documento_paciente_odontologo); 
            Mail::to($email)->send($correo_odontologo);
            return response()->json(['status'=>"success"], 200);
        }else{
            return response()->json(['status'=>"no_exise"]);
        }

    }
}

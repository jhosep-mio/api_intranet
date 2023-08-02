<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Notification;
use App\Mail\NotificationDoctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class mailController extends Controller
{
    public function enviarCorreo(Request $request){
        $request->validate([
            'name'=>'required|string',
            'nameDoctor'=>'required|string',
            'user'=>'required|string',
            'pass' =>'required|string',
            'email' =>'required|string',
            'user_odontologo' =>'required|string',
            'pass_odontologo' =>'required|string',
            'email_odontologo' =>'required|string',
        ]);
        $name = $request->name;
        $nameDoctor = $request->nameDoctor;
        $user = $request->user;
        $pass = $request->pass;
        $email = $request->email;
        $user_odontologo = $request->user_odontologo;
        $pass_odontologo = $request->pass_odontologo;
        $email_odontologo = $request->email_odontologo;
        $correo = new Notification($name,$user,$pass); 
        $correo_odontologo = new NotificationDoctor($nameDoctor,$name,$user_odontologo,$pass_odontologo); 
        Mail::to($email)->send($correo);
        Mail::to($email_odontologo)->send($correo_odontologo);
        // Devuelve una respuesta adecuada a React
        return response()->json(['status'=>"success"], 200);
    }
}

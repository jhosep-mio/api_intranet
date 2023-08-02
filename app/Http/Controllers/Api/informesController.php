<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\informes;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
class informesController extends Controller
{
    public function index(){
        $registros = informes::all();
        return $registros;
    }

    public function store(Request $request){
        $request->validate([
            'id_orden'=>'required|integer',
            'id_servicio'=>'required|integer',
            'informe' =>'required',
        ]);

        if ($request->hasFile('informe')) {
            $files = $request->file('informe');
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $saveInforme = new informes();
                    $nombreInforme = time().'_'.$file->getClientOriginalName();
                    $file->move(public_path('informes/'),$nombreInforme);
                    $saveInforme->id_orden = $request->id_orden;
                    $saveInforme->id_servicio = $request->id_servicio;
                    $saveInforme->informe = $nombreInforme;
                    $result = $saveInforme->save();
                }
            }
        }
        if($result){
            return response()->json(['status'=>"success"], 200);
        }else {
            return response()->json(['status'=>"error"]);
        }
    }

    public function destroy($id){
        $verArchivo = informes::find($id);
        if($verArchivo){
            $imagen = $verArchivo->informe;
            $rutaArchivo = public_path('informes/'.$imagen);
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }else{
                return response()->json(['status'=>"error_images"]);
            }
        }
        
        $result = informes::destroy($id);

        if($result){
            return response()->json(['status'=>"success"]);
        }else{
            return response()->json(['status'=>"error"]);
        }
    }

    public function getInformes($id){
        $verArchivo = informes::where('id_orden', $id)->pluck('informe');
        $imagenesPath = [];
        foreach ($verArchivo as $imagen) {
            $rutaImagen = public_path('informes/'.$imagen); // Obtener la ruta pública de la imagen
            $imagenesPath[] = $rutaImagen; // Agregar la ruta al array de rutas de imágenes
        }
        $zipName = 'informes.zip';
        $zip = new \ZipArchive();
        $zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE); // Agrega la bandera OVERWRITE para asegurarte de que el archivo ZIP se cree correctamente
        foreach ($imagenesPath as $index => $imagenPath) {
            $archivoNombre = 'informe' . ($index + 1) . '.' . pathinfo($imagenPath, PATHINFO_EXTENSION);
            $zip->addFromString($archivoNombre, file_get_contents($imagenPath));
        }
        $zip->close();
        return response()->download($zipName)->deleteFileAfterSend(true);
    }   

    public function destroyAll($id){
        $verArchivo = informes::where('id_orden', $id)->pluck('informe');
        foreach ($verArchivo as $imagen) {
            if($imagen){
                $rutaArchivo = public_path('informes/'.$imagen);
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
            }
        }
        $result = informes::where('id_orden', $id)->delete();

        if($result){
            return response()->json(['status'=>"success"]);
        }else{
            return response()->json(['status'=>"error"]);
        }
    }

    public function getImagenesGroupServicesInformes(Request $request){
        $request->validate([
           'id_orden'=>'required|integer',
           'id_servicio'=>'required|integer',
       ]);
       $id_orden = $request->id_orden;
       $id_servicio = $request->id_servicio;
       // $verArchivo = archivos::where('id_orden', $request->id_orden)->pluck('archivo');
       $verArchivo = informes::where('id_orden', $id_orden)->where('id_servicio', $id_servicio)->pluck('informe');
       $imagenesPath = [];
       foreach ($verArchivo as $imagen) {
           $rutaImagen = public_path('informes/'.$imagen); // Obtener la ruta pública de la imagen
           $imagenesPath[] = $rutaImagen; // Agregar la ruta al array de rutas de imágenes
       }
       $zipName = 'informe.zip';
       $zip = new \ZipArchive();
       $zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE); // Agrega la bandera OVERWRITE para asegurarte de que el archivo ZIP se cree correctamente
       foreach ($imagenesPath as $index => $imagenPath) {
           $archivoNombre = 'informe' . ($index + 1) . '.' . pathinfo($imagenPath, PATHINFO_EXTENSION);
           $zip->addFromString($archivoNombre, file_get_contents($imagenPath));
       }
       $zip->close();
       return response()->download($zipName)->deleteFileAfterSend(true);
    }   

    public function dowloadInformes($id){
        $filename = informes::find($id)->informe;
        // Verifica si el archivo existe
        $path = public_path('informes/'. $filename);
        // Descarga el archivo ZIP con el mismo nombre
        // Crea una instancia de la respuesta
        $response = response()->file($path);
        // Establece el nombre de archivo en el encabezado Content-Disposition
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            mb_convert_encoding($filename, 'ASCII', 'UTF-8')
        ));
        return $response;
    }   

    public function show($id){
        $verPaciente = informes::find($id);
        return $verPaciente;
    }

}

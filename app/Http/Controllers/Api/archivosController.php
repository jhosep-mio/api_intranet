<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\archivos;
use App\Models\informes;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Events\ProgressEvent; // Importa el evento ProgressEvent

class archivosController extends Controller
{
    public function index()
    {
        $registros = archivos::all();
        return $registros;
    }

    public function indexImages()
    {
        $images = [];
        $registros = archivos::select('archivo')->get();

        foreach ($registros as $file) {
            $images[] = "http://127.0.0.1:8000/imagenes/" . $file;
        }
        return $images;
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_orden' => 'required|integer',
            'archivo' => 'required',
        ]);

        if ($request->hasFile('archivo')) {
            $files = $request->file('archivo');
            foreach ($files as $file) {
                if ($file->isValid()) {
                    $saveArchivo = new archivos();
                    $nombreArchivo = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('imagenes/'), $nombreArchivo);
                    $saveArchivo->id_orden = $request->id_orden;
                    $saveArchivo->id_servicio = 1;
                    $saveArchivo->archivo = $nombreArchivo;
                    $result = $saveArchivo->save();
                }
            }
        }
        if ($result) {
            return response()->json(['status' => "success"], 200);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function show($id)
    {
        $verArchivo = archivos::find($id);
        return $verArchivo;
    }

    public function destroy($id)
    {
        $verArchivo = archivos::find($id);
        if ($verArchivo) {
            $imagen = $verArchivo->archivo;
            $rutaArchivo = public_path('imagenes/' . $imagen);
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            } else {
                return response()->json(['status' => "error_images"]);
            }
        }

        $result = archivos::destroy($id);

        if ($result) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function getImagenes($id)
    {
        $archivos = archivos::where('id_orden', $id)->get();
        $informes = informes::where('id_orden', $id)->get();

        $zipName = 'archivos.zip';
        $zip = new \ZipArchive();
        $zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        // Agregar carpeta de imágenes al archivo ZIP
        $imagenesFolder = 'Imágenes';
        $zip->addEmptyDir($imagenesFolder);
        $archivosNames = []; // Para almacenar los nombres de los archivos y evitar duplicados

        $currentFile = 0;
        $totalSize = 0;
        $totalFiles = count($archivos) + count($informes);
    
        foreach ($archivos as $archivo) {
            $rutaArchivo = public_path('imagenes/' . $archivo->archivo);
            $totalSize += filesize($rutaArchivo);
            $nombreArchivo = substr($archivo->archivo, strpos($archivo->archivo, '_') + 1);

            // Verificar si el nombre del archivo ya existe, si es así, agregar un sufijo numérico
            $contador = 1;
            $nombreArchivoOriginal = pathinfo($nombreArchivo, PATHINFO_FILENAME);
            $extensionArchivo = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
            while (in_array($nombreArchivo, $archivosNames)) {
                $nombreArchivo = $nombreArchivoOriginal . '(' . $contador . ').' . $extensionArchivo;
                $contador++;
            }
            $archivosNames[] = $nombreArchivo;

            $currentFile++;
            $progress = round(($currentFile / $totalFiles) * 100);
            event(new ProgressEvent($progress, 100));

            $zip->addFile($rutaArchivo, $imagenesFolder . '/' . $nombreArchivo);
        }

        // Agregar carpeta de informes al archivo ZIP
        $informesFolder = 'Informes';
        $zip->addEmptyDir($informesFolder);
        $informesNames = []; // Para almacenar los nombres de los informes y evitar duplicados
        foreach ($informes as $informe) {
            $rutaInforme = public_path('informes/' . $informe->informe);
            $totalSize += filesize($rutaInforme);
            $nombreInforme = substr($informe->informe, strpos($informe->informe, '_') + 1);

            // Verificar si el nombre del informe ya existe, si es así, agregar un sufijo numérico
            $contador = 1;
            $nombreInformeOriginal = pathinfo($nombreInforme, PATHINFO_FILENAME);
            $extensionInforme = pathinfo($nombreInforme, PATHINFO_EXTENSION);
            while (in_array($nombreInforme, $informesNames)) {
                $nombreInforme = $nombreInformeOriginal . '(' . $contador . ').' . $extensionInforme;
                $contador++;
            }
            $informesNames[] = $nombreInforme;

            $currentFile++;
            $progress = round(($currentFile / $totalFiles) * 100);
            event(new ProgressEvent($progress, 100));

            $zip->addFile($rutaInforme, $informesFolder . '/' . $nombreInforme);
        }

        $zip->close();

        return response()->download($zipName, null, [
            'Content-Length' => $totalSize,
        ])->deleteFileAfterSend(true);
    }


    public function getZipSize($id)
    {
        $archivos = archivos::where('id_orden', $id)->get();
        $informes = informes::where('id_orden', $id)->get();

        $totalSize = 0;
    
        foreach ($archivos as $archivo) {
            $rutaArchivo = public_path('imagenes/' . $archivo->archivo);
            $totalSize += filesize($rutaArchivo);
        }

        foreach ($informes as $informe) {
            $rutaInforme = public_path('informes/' . $informe->informe);
            $totalSize += filesize($rutaInforme);
        }
        return response()->json(['size' => $totalSize]);
    }
    
    public function destroyAll($id)
    {
        $verArchivo = archivos::where('id_orden', $id)->pluck('archivo');
        foreach ($verArchivo as $imagen) {
            if ($imagen) {
                $rutaArchivo = public_path('imagenes/' . $imagen);
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
            }
        }

        $result = archivos::where('id_orden', $id)->delete();

        $verArchivo2 = informes::where('id_orden', $id)->pluck('informe');
        foreach ($verArchivo2 as $imagen2) {
            if ($imagen2) {
                $rutaArchivo2 = public_path('informes/' . $imagen2);
                if (file_exists($rutaArchivo2)) {
                    unlink($rutaArchivo2);
                }
            }
        }
        $result2 = informes::where('id_orden', $id)->delete();


        if ($result2) {
            return response()->json(['status' => "success"]);
        } else {
            return response()->json(['status' => "error"]);
        }
    }

    public function getImagenesGroupServices(Request $request)
    {
        $request->validate([
            'id_orden' => 'required|integer',
            'id_servicio' => 'required|integer',
        ]);
        $id_orden = $request->id_orden;
        $id_servicio = $request->id_servicio;

        $archivos = archivos::where('id_orden', $id_orden)->where('id_servicio', $id_servicio)->get();
        $informes = informes::where('id_orden', $id_orden)->where('id_servicio', $id_servicio)->get();

        $zipName = 'archivos.zip';
        $zip = new \ZipArchive();
        $zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Agregar carpeta de imágenes al archivo ZIP
        $imagenesFolder = 'Imagenes';
        $zip->addEmptyDir($imagenesFolder);
        $archivosNames = []; // Para almacenar los nombres de los archivos y evitar duplicados
        foreach ($archivos as $archivo) {
            $rutaArchivo = public_path('imagenes/' . $archivo->archivo);
            $nombreArchivo = substr($archivo->archivo, strpos($archivo->archivo, '_') + 1);

            // Verificar si el nombre del archivo ya existe, si es así, agregar un sufijo numérico
            $contador = 1;
            $nombreArchivoOriginal = pathinfo($nombreArchivo, PATHINFO_FILENAME);
            $extensionArchivo = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
            while (in_array($nombreArchivo, $archivosNames)) {
                $nombreArchivo = $nombreArchivoOriginal . '(' . $contador . ').' . $extensionArchivo;
                $contador++;
            }
            $archivosNames[] = $nombreArchivo;

            $zip->addFile($rutaArchivo, $imagenesFolder . '/' . $nombreArchivo);
        }

        // Agregar carpeta de informes al archivo ZIP
        $informesFolder = 'Informes';
        $zip->addEmptyDir($informesFolder);
        $informesNames = []; // Para almacenar los nombres de los informes y evitar duplicados
        foreach ($informes as $informe) {
            $rutaInforme = public_path('informes/' . $informe->informe);
            $nombreInforme = substr($informe->informe, strpos($informe->informe, '_') + 1);

            // Verificar si el nombre del informe ya existe, si es así, agregar un sufijo numérico
            $contador = 1;
            $nombreInformeOriginal = pathinfo($nombreInforme, PATHINFO_FILENAME);
            $extensionInforme = pathinfo($nombreInforme, PATHINFO_EXTENSION);
            while (in_array($nombreInforme, $informesNames)) {
                $nombreInforme = $nombreInformeOriginal . '(' . $contador . ').' . $extensionInforme;
                $contador++;
            }
            $informesNames[] = $nombreInforme;

            $zip->addFile($rutaInforme, $informesFolder . '/' . $nombreInforme);
        }

        $zip->close();

        return response()->download($zipName)->deleteFileAfterSend(true);
    }
}

<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarRegistrosAntiguos extends Command
{
    protected $signature = 'registros:limpiar';

    protected $description = 'Limpia los registros antiguos en la tabla password_resets';

    public function handle()
    {
        $fechaLimite = Carbon::now()->subMinutes(10); // Eliminar registros más antiguos de 24 horas
        DB::table('password_resets')
            ->where('created_at', '<', $fechaLimite)
            ->delete();

        $this->info('Registros antiguos eliminados satisfactoriamente.');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PrintGetSucs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:getsucursales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar batch para cargar sucursales en instalador de Windows';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      echo "Descargando la lista de sucursales desde: ".env('API_URL') ."  \n";
      $response = Http::withOptions([
        'verify' => false,
      ])
      ->get(env('API_URL').'/api/printTickets/sucursales', [
        // 'sucursal_id' => env('SUCURSAL_ID')
      ]);
      $sucursales = $response->collect();
      $i =0;
      $preChoice = null;
      $opts = range('A','Z');
      $optsn = [];
      $sucursales->forget('message');
      // dd($sucursales['message']);
      Storage::put('sucursales.bat', '@ECHO off')
      Storage::append('REM Script para sucursales Autogenerado con laravel', '');
      foreach ($sucursales as $sucursal) {
        Storage::append('sucursales.bat', 'ECHO '. $opts[$i] . ' - '.$sucursal['nombre']);
        $preChoice = $preChoice.$opts[$i];
        $optsn[$i] = $opts[$i];
        $i++;
      }
      Storage::append('sucursales.bat', '');
      Storage::append('sucursales.bat','CHOICE /C '. $preChoice.' /M "Seleccione su sucursal para iniciar la instalacion..." /N');
      Storage::append('sucursales.bat', '');
      $j = $sucursales->count()-1;
      foreach ($sucursales->reverse() as $sucursal) {
        Storage::append('sucursales.bat', 'IF ERRORLEVEL  '. $j+1 . ' GOTO SUC'.$optsn[$j]);
        $j--;
      }
      $i = 0;
      Storage::append('sucursales.bat', '');
      foreach ($sucursales as $sucursal) {
        Storage::append('sucursales.bat', ':SUC'. $optsn[$i]);
        Storage::append('sucursales.bat', 'ECHO Usted seleccionó: '. $sucursal['nombre']);
        Storage::append('sucursales.bat', 'SET SUCURSAL = '. $sucursal['id']);
        Storage::append('sucursales.bat', 'GOTO SETSUCURSAL ');
        $i++;
      }
      echo "Lista de sucursales cargada con éxito... \n";
      // dd($sucursales->search('ok'));
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:testapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la conectividad con el sitio web origen de los datos para impresión';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Storage::delete(['sucapi.txt', 'tikapi.txt', 'printerapi.txt', 'updaterapi.txt']);

        echo "Comprobando la conectividad con la API de sucursales... \n";
        $response = Http::withOptions([
          'verify' => false,
        ])
        ->get(env('API_URL').'/api/printTickets/sucursales', [
          // 'sucursal_id' => env('SUCURSAL_ID')
        ]);

        $tesSuc=$response->collect();
        if ($tesSuc['message']=='ok') {
          Storage::put('sucapi.txt', '');
          echo "Resultado: API de sucursales en linea... \n";
        }else {
          echo "Resultado: API de sucursales fuera de linea... \n";
        }

        echo "Comprobando la conectividad con la API de tickets para imprimir... \n";
        $response = Http::withOptions([
          'verify' => false,
        ])
        ->post(env('API_URL').'/api/printTickets/print', [

        ]);

        $tesSuc=$response->collect();
        if ($tesSuc['message']=='No hay tickets que imprimir') {
          Storage::put('tikapi.txt', '');
          echo "Resultado: API de tickets para imprimir en linea... \n";
        }else {
          echo "Resultado: API de tickets para imprimir fuera de linea... \n";
        }

        echo "Comprobando la conectividad con la API de impresoras... \n";
        $response = Http::withOptions([
          'verify' => false,
        ])
        ->get(env('API_URL').'/api/printTickets/ipprint', [

        ]);

        $tesSuc=$response->collect();
        if ($tesSuc['message']=='No existe la impresora') {
          Storage::put('printerapi.txt', '');
          echo "Resultado: API de impresoras en linea... \n";
        }else {
          echo "Resultado: API de impresoras fuera de linea... \n";
        }

        echo "Comprobando la conectividad con la API de update... \n";
        $response = Http::withOptions([
          'verify' => false,
        ])
        ->post(env('API_URL').'/api/printTickets/printed', [

        ]);

        $tesSuc=$response->collect();
        if ($tesSuc['message']=='No existe el ticket') {
          Storage::put('updaterapi.txt', '');
          echo "Resultado: API de update en linea... \n";
        }else {
          echo "Resultado: API de update fuera de linea... \n";
        }



    }
}

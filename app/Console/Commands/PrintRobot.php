<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use App\Customlib\item;


class PrintRobot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:ticket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imprime los tickets cuando son registrados en la API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $response = Http::withOptions([
        'verify' => false,
      ])
      ->post(env('API_URL').'/api/printTickets/print', [
        'sucursal_id' => env('SUCURSAL_ID')
      ]);

      $responseCollect=$response->collect();
      if ($responseCollect['message']=='ok') {

        $total = 0;
        $productos = $responseCollect[0]['datat']['Productos'];
        foreach ($productos as $producto) {
            $items[] = new item($producto['Cantidad'],$producto['Nombre'],$producto['Precio']);
            $total += $producto["Cantidad"] * $producto["Precio"];
        }


        try {
            $logo = EscposImage::load(public_path('/theme/img/brand/logob.png'));
            $responseIp = Http::withOptions([
              'verify' => false,
            ])
            ->get(env('API_URL').'/api/printTickets/ipprint', [
              'sucursal_id' => env('SUCURSAL_ID')
            ]);
            $responseIpCollect=$responseIp->collect();
            $ipToPrint=$responseIpCollect[0]['ip_print'];
            $connector = new NetworkPrintConnector($ipToPrint);
            $printer = new Printer($connector);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> graphics($logo);
            $printer -> text("\n");
            $printer -> selectPrintMode();
            $printer -> setEmphasis(true);
            $printer -> text("YOGOPOINTS \n");
            $printer -> selectPrintMode();
            $printer -> text("Ticket de venta: ".$responseCollect[0]['datat']['Id'] ." \n");
            $printer -> selectPrintMode();
            $printer -> text($responseCollect[0]['datat']['Fecha'] ." \n");
            $printer -> selectPrintMode();
            $printer -> text($responseCollect[0]['datat']['Sucursal'] ." \n");
            $printer -> selectPrintMode();
            $printer -> text($responseCollect[0]['datat']['Cajero'] ." \n");
            $printer -> selectPrintMode();
            $printer -> text($responseCollect[0]['datat']['Apodo'] ." \n");
            $printer -> selectPrintMode();
            $printer -> text($responseCollect[0]['datat']['Email'] ." \n");
            $printer -> selectPrintMode();
            $printer -> text("SELLOS: ". $responseCollect[0]['datat']['Sellos'] ." \n");
            $printer -> text(str_pad('',  40, "-")." \n");
            $printer -> text(str_pad('CANT',  4, " "));
            $printer -> text(str_pad('PRODUCTO',  29, " ", STR_PAD_BOTH));
            $printer -> text(str_pad('$$',  7, " ", STR_PAD_LEFT) ." \n");
            $printer -> text(str_pad('',  40, "-")." \n");
            $printer -> setEmphasis(false);
            foreach ($items as $item) {
                $printer -> text($item);
            }
            $printer -> setEmphasis(true);
            $printer -> text(str_pad('',  40, "-")." \n");
            $printer -> text(str_pad('TOTAL',  33, " ", STR_PAD_BOTH));
            $printer -> text(str_pad("$".$total,  7, " ", STR_PAD_LEFT) ." \n");
            $printer -> setEmphasis(false);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("¡GRACIAS POR SU COMPRA!\n");
            $printer -> text(" www.yogocup.com ");
            $printer -> feed(2);
            $printer -> cut();
            $printer -> pulse();
            $printer -> close();
            Log::info('recibo impreso: '. $responseCollect[0]['datat']['Id'] );
            $respUpdate = Http::withOptions([
              'verify' => false,
            ])
            ->post(env('API_URL').'/api/printTickets/printed', [
              'archivo' => $responseCollect[0]['datat']['Id']
            ]);
            Log::info('bd actualizada: '. $responseCollect[0]['datat']['Id'] );
        } catch (\Exception $e) {
            if (isset($printer)) {
              $printer -> close();
            }
            echo $e -> getMessage() . "\n";
            Log::info('error de impresión: '. $e  . "\n" );
            // return $e;
        } finally {

        }

      }else {
        // Log::info($responseCollect['message'] );
      }
    }
}

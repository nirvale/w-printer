<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Illuminate\Support\Facades\Log;
use App\Customlib\item;


class PrinterController extends Controller
{

    public function endpointer()
    {
      $response = Http::post(env('API_URL').'/printTickets/print', [
        'sucursal_id' => env('SUCURSAL_ID')
      ]);
      $path = public_path('/theme/img/brand/logo.png');
      $responseCollect=$response->collect();
      if ($responseCollect['message']=='ok') {
        $dataT = array(
            'imagen'=> $path,
            'Productos'=>$responseCollect[0]['datat']['Productos'],
            'Id'=>$responseCollect[0]['datat']['Id'],
            "Fecha"=>$responseCollect[0]['datat']['Fecha'],
            "Cajero"=>$responseCollect[0]['datat']['Cajero'],
            "Sucursal"=>$responseCollect[0]['datat']['Sucursal'],
            "Sellos"=>$responseCollect[0]['datat']['Sellos'],
            "Cupones"=>$responseCollect[0]['datat']['Cupones'],
            "Apodo"=>$responseCollect[0]['datat']['Apodo'],
            "Email"=>$responseCollect[0]['datat']['Email']
        );
        $pdf = PDF::loadView('print.ticket', $dataT);
        $pdf->setPaper('b7', 'portrait');
        $pdf->save(storage_path('app/public/tickets/'.$responseCollect[0]['datat']['Id'] .'.pdf'));
        $total = 0;
        $productos = $responseCollect[0]['datat']['Productos'];
        foreach ($productos as $producto) {
            $items[] = new item($producto['Cantidad'],$producto['Nombre'],$producto['Precio']);
            $total += $producto["Cantidad"] * $producto["Precio"];
        }
        $logo = EscposImage::load(public_path('/theme/img/brand/logob.png'));

        try {
            $responseIp = Http::get(env('API_URL').'/printTickets/ipprint', [
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
            $respUpdate = Http::post(env('API_URL').'/printTickets/printed', [
              'archivo' => $responseCollect[0]['datat']['Id']
            ]);
        } catch (\Exception $e) {
            if (isset($printer)) {
              $printer -> close();
            }
            echo $e -> getMessage() . "\n";
            // return $e;
        } finally {

        }

        Log::info('recibo impreso: '. $responseCollect[0]['datat']['Id'] );

      }else {
        Log::info($responseCollect['message'] );
      }

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

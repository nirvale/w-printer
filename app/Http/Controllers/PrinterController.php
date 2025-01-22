<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Illuminate\Support\Facades\Log;


class PrinterController extends Controller
{

    public function endpointer()
    {
      $response = Http::post(env('API_URL').'/printTickets/print', [
        'sucursal_id' => env('SUCURSAL_ID')
      ]);
      $path = public_path('/theme/img/brand/logo.png');
      // $type = pathinfo($path, PATHINFO_EXTENSION);
      // $data = file_get_contents($path);
      // $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
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

        $responseIp = Http::get(env('API_URL').'/printTickets/ipprint', [
          'sucursal_id' => env('SUCURSAL_ID')
        ]);
        $responseIpCollect=$responseIp->collect();
        $ipToPrint=$responseIpCollect[0]['ip_print'];
        $pdfTp = storage_path('app/public/tickets/'.$responseCollect[0]['datat']['Id'] .'.pdf');
        $connector = new NetworkPrintConnector($ipToPrint);
        $printer = new Printer($connector);
        try {
            $pagesImg = new \Spatie\PdfToImage\Pdf($pdfTp);
            $pages = $pagesImg->saveAllPages(storage_path('app/public/tickets/'),$responseCollect[0]['datat']['Id'].'_');
            foreach ($pages as $page) {
                $tux = EscposImage::load($page, false);
                $printer -> graphics($tux);
            }
            $printer -> cut();
            $printer -> close();
            $respUpdate = Http::post(env('API_URL').'/printTickets/printed', [
              'archivo' => $responseCollect[0]['datat']['Id']
            ]);
        } catch (Exception $e) {
            /*
           * loadPdf() throws exceptions if files or not found, or you don't have the
           * imagick extension to read PDF's
           */
           $printer -> close();
            echo $e -> getMessage() . "\n";
        } finally {
          $printer -> close();
        }
        $printer -> close();

        // return response(json_encode(["Success"=>true,"Ticket"=>'/storage/tickets/'.$responseCollect[0]['datat']['Id'].'.pdf']),200);

          Log::info('recibo impreso: '. $responseCollect[0]['datat']['Id'] );

          // dd($responseCollect[0]->datat->Productos[0]->Nombre);

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

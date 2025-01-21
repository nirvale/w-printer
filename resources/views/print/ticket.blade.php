<?php
$medidaTicket = 280;

?>
    <!DOCTYPE html>
{{-- <html> --}}

<head>

    <style>
        * {
            font-size: 12px;
            font-family: 'DejaVu Sans', serif;
        }

        h1 {
            font-size: 18px;
        }

        .ticket {
            margin: 2px;
        }

        td,
        th,
        tr,
        table {
            border-top: 1px solid black;
            border-collapse: collapse;
            margin: 0 auto;
        }

        td.precio {
            text-align: right;
            font-size: 11px;
        }

        td.cantidad {
            font-size: 11px;
        }

        td.producto {
            text-align: center;
        }

        th {
            text-align: center;
        }


        .centrado {
            text-align: center;
            align-content: center;
        }

        .ticket {
            width: <?php echo $medidaTicket ?>px;
            max-width: <?php echo $medidaTicket ?>px;
        }

        img {
            max-width: inherit;
            width: inherit;
        }

        * {
            margin: 0;
            padding: 0;
        }

        .ticket {
            margin: 5;
            padding: 10;
        }

        body {
            text-align: center;
        }
    </style>
</head>

{{-- <body> --}}
<div class="ticket centrado">
    <img src="{{$imagen}}">
    <h1><b style="color: rebeccapurple">YOGOPOINTS</b></h1>
    <h2>Ticket de venta #{{$Id}}</h2>
    <h2>{{$Fecha}}</h2>
    <small>{{$Sucursal}}</small><br>
    <small>{{$Cajero}}</small><br>
    <small>{{$Apodo}}</small><br>
    <small>{{$Email}}</small><br>
    <h5>Sellos: {{$Sellos}}</h5>
    <table>
        <thead>
        <tr class="centrado">
            <th class="cantidad">CANT</th>
            <th class="producto">PRODUCTO</th>
            <th class="precio">$$</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $total = 0;
        foreach ($Productos as $producto) {
        $total += $producto["Cantidad"] * $producto["Precio"];
        ?>
        <tr>
            <td class="cantidad" style="text-align: center; vertical-align: middle;"><?php echo number_format($producto["Cantidad"], 2) ?></td>
            <td class="producto"><?php echo $producto["Nombre"] ?><br><?php echo $producto["Gramaje"] ?> grs.</td>
            <td class="precio">$<?php echo number_format($total, 2) ?></td>
        </tr>

        <?php } ?>
        <?php
        $totalC = 0;
        foreach ($Cupones as $cupon) {
        $totalC += 1 * $cupon["Descuento"];
        ?>
        <tr>
            <td class="cantidad" style="text-align: center; vertical-align: middle;">1</td>
            <td class="producto"><?php echo $cupon["Premio"] ?></td>
            <td class="precio">-$<?php echo number_format($cupon["Descuento"], 2) ?></td>
        </tr>

        <?php } ?>
        </tbody>
        <tr>
            <td class="cantidad"></td>
            <td class="producto">
                <strong>TOTAL</strong>
            </td>
            <td class="precio">
                $<?php echo number_format($total - $totalC, 2) ?>
            </td>
        </tr>
    </table>
    <p class="centrado">¡GRACIAS POR SU COMPRA!
        <br>&#9829; www.yogocup.com &#9829;
    </p>
</div>
{{-- </body> --}}
<script>
    window.close();
</script>
{{-- </html> --}}

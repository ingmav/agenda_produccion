<?php
header('Content-type: application/json; charset=utf-8');
$Servidor = "localhost";
$Ruta     = "C:\\Microsip datos\\NEXPRINT.FDB";
$Usuario  = "SYSDBA";
$Pass	  = "masterkey";
try {
    $conexion = ibase_connect($Servidor.":".$Ruta, $Usuario, $Pass,  "UTF8", 0, 3);
} catch (PDOException $e) {
    echo 'Connection failed'.$e->getMessage();
}

$json = file_get_contents('php://input');
$data=json_decode($json);

if($data->accion == 'programar_fecha')
{
    if($data->empresa == 1)
        $sql = "update tableroproduccion set fecha_programado=fecha_entrega where id=".$data->id;
    if($data->empresa == 2)
        $sql = "update produccionpv set fecha_programado=fecha_entrega where id=".$data->id;    
    $resultado = ibase_query($conexion, $sql);
}

if($data->accion == 'programar_fecha_tarea')
{
    $fecha_formateado = str_replace(['T'], ' ', $data->fecha_programacion);
    if($data->empresa == 1)
        $sql = "update tableroproduccion set fecha_programado='$fecha_formateado' where id=".$data->id;
    if($data->empresa == 2)
        $sql = "update produccionpv set fecha_programado='$fecha_formateado' where id=".$data->id;    
    
    $resultado = ibase_query($conexion, $sql);
}


if($data->accion == 'detalles')
{
    if($data->empresa == 1)
        $sql = "select a.nombre, dvd.notas, dvd.unidades from doctos_ve_det dvd, articulos a where dvd.articulo_id = a.articulo_id and dvd.docto_ve_id=".$data->id;
   
    if($data->empresa == 2)
        $sql = "select a.nombre, dvd.notas, dvd.unidades from doctos_pv_det dvd, articulos a where dvd.articulo_id = a.articulo_id and dvd.docto_pv_id=".$data->id; 

    // Execute query
    $resultado = ibase_query($conexion, $sql);
    // Get the result row by row as object
    $respuesta = [];
   
    while ($row = ibase_fetch_object($resultado, IBASE_TEXT)) {
        $index = count($respuesta);
        $respuesta[$index]['nombre_articulo'] = utf8_encode($row->NOMBRE);
        $respuesta[$index]['nota'] = utf8_encode($row->NOTAS);
        $respuesta[$index]['cantidad'] = $row->UNIDADES;
    }
   
    echo json_encode($respuesta);
}

if($data->accion == 'catalogos')
{
    $fecha_semana_1 = date("Y").'-01-01';
    $semanas_anio = [];
    $fecha_actual = date("Y-m-d");
    $semana_actual = (int)date("W", strtotime($fecha_actual));
    $semana_1 = inicio_fin_semana($fecha_semana_1);
    $semanas_anio = calculo_semanas_anio($semana_1['fechaInicio'], $semana_actual);

    
    $arreglo_salida = array("semanas"=>$semanas_anio);
    echo json_encode($arreglo_salida);
}

function inicio_fin_semana($fecha){

    $diaInicio="Monday";
    $diaFin="Sunday";

    $strFecha = strtotime($fecha);

    $fechaInicio = date('Y-m-d',strtotime('last '.$diaInicio,$strFecha));
    $fechaFin = date('Y-m-d',strtotime('next '.$diaFin,$strFecha));

    if(date("l",$strFecha)==$diaInicio){
        $fechaInicio= date("Y-m-d",$strFecha);
    }
    if(date("l",$strFecha)==$diaFin){
        $fechaFin= date("Y-m-d",$strFecha);
    }
    return Array("fechaInicio"=>$fechaInicio,"fechaFin"=>$fechaFin);
}

function calculo_semanas_anio($fecha, $actual){

    $arreglo_semanas = [];
    $lunes_inicial = $fecha;
    for($i = 1; $i<=52; $i++)
    {
        $domingo_siguiente = date("Y-m-d", strtotime("$lunes_inicial   +6 day"));
        $arreglo_semanas[] = array("id"=>$i, "descripcion"=>'del '.$lunes_inicial." al ".$domingo_siguiente, 'select'=>$actual);
        $lunes_inicial = date("Y-m-d", strtotime("$lunes_inicial   +7 day"));
    }

    return $arreglo_semanas;
   
}
?>
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


    /*$sql = "select tp.id,
    dv.folio,
    dv.fecha,
    cl.nombre,
    dv.descripcion,
    tp.fecha_entrega,
    tp.gf_diseno,
    tp.diseno_gf,
    tp.gf_impresion,
    tp.impresion_gf,
    tp.gf_preparacion,
    tp.preparacion_gf,
    tp.gf_entrega,
    tp.entrega_gf,
    tp.gf_instalacion,
    tp.instalacion_gf,
    tp.gf_maquilas,
    tp.maquilas_gf,
    fecha_programado,
    1 as empresa
    from tableroproduccion tp,
    doctos_ve dv,
    clientes cl
    
    where
    tp.docto_ve_id = dv.docto_ve_id
    and dv.cliente_id = cl.cliente_id
    and cerrar_seleccion=1 and finalizar_proceso=0
    and (tp.gf_instalacion = 1 or tp.gf_entrega= 1)
    order by dv.fecha desc";*/

    $sql = "select id,
    id_venta,
    folio,
    fecha,
    nombre,
    descripcion,
    fecha_entrega,
    gf_diseno,
    diseno_gf,
    gf_impresion,
    impresion_gf,
    gf_preparacion,
    preparacion_gf,
    gf_instalacion,
    instalacion_gf,
    gf_entrega,
    entrega_gf,
    gf_maquilas,
    maquilas_gf,
    fecha_programado,
    empresa from
    (select tp.id,
    dv.docto_ve_id as id_venta,
    dv.folio,
    dv.fecha,
    cl.nombre,
    dv.descripcion,
    tp.fecha_entrega,
    tp.gf_diseno,
    tp.diseno_gf,
    tp.gf_impresion,
    tp.impresion_gf,
    tp.gf_preparacion,
    tp.preparacion_gf,
    tp.gf_entrega,
    tp.entrega_gf,
    tp.gf_instalacion,
    tp.instalacion_gf,
    tp.gf_maquilas,
    tp.maquilas_gf,
    fecha_programado,
    1 as empresa
    from tableroproduccion tp,
    doctos_ve dv,
    clientes cl
    where
    tp.docto_ve_id = dv.docto_ve_id
    and dv.estatus != 'C'
    and dv.cliente_id = cl.cliente_id
    and cerrar_seleccion=1 and finalizar_proceso=0
    and (tp.gf_instalacion = 1 or tp.gf_entrega= 1)
    UNION all
    select tp.id,
    dv.docto_pv_id as id_venta,
    dv.folio,
    dv.fecha,
    cl.nombre,
    dv.descripcion,
    tp.f_entrega as fecha_entrega,
    tp.gf_diseno,
    tp.diseno_gf,
    tp.gf_impresion,
    tp.impresion_gf,
    tp.gf_preparacion,
    tp.preparacion_gf,
    tp.gf_entrega,
    tp.entrega_gf,
    tp.gf_instalacion,
    tp.instalacion_gf,
    0 as gf_maquilas,
    0 as maquilas_gf,
    fecha_programado,
    2 as empresa
    from PRODUCCIONPV tp,
    doctos_pv dv,
    clientes cl
    where
    tp.docto_pv_id = dv.docto_pv_id
    and dv.estatus != 'C'
    and dv.cliente_id = cl.cliente_id
    and cerrar_seleccion=1 and finalizar_proceso=0
    and (tp.gf_instalacion = 1 or tp.gf_entrega= 1)
    ) order by fecha_entrega";
    // Execute query
    $resultado = ibase_query($conexion, $sql);
    // Get the result row by row as object
    $respuesta = [];
    if($data->semana)
    {
        $year = date("Y");
        $week = $data->semana;
        $fechaInicioSemana  = date('d-m-Y', strtotime($year . 'W' . str_pad($week , 2, '0', STR_PAD_LEFT)));

        $fecha_actual = date('d-m-Y',strtotime("$fechaInicioSemana   +6 day"));
    }else
        $fecha_actual = date("Y-m-d");
    $semana_actual = date("W", strtotime($fecha_actual));
    //print_r($semana_actual);
    while ($row = ibase_fetch_object($resultado)) {
        
        $index = count($respuesta);
        $respuesta[$index]['id'] = $row->ID;
        $respuesta[$index]['id_venta'] = $row->ID_VENTA;
        $respuesta[$index]['folio'] = $row->FOLIO;
        $respuesta[$index]['fecha'] = $row->FECHA;
        $respuesta[$index]['nombre'] = utf8_encode($row->NOMBRE);
        $respuesta[$index]['descripcion'] = utf8_encode($row->DESCRIPCION);
        $respuesta[$index]['fecha_entrega'] = $row->FECHA_ENTREGA;
        
        $respuesta[$index]['fecha_programacion'] = $row->FECHA_PROGRAMADO;
        $respuesta[$index]['gf_diseno'] = $row->GF_DISENO;
        $respuesta[$index]['diseno_gf'] = $row->DISENO_GF;
        $respuesta[$index]['impresion_gf'] = $row->IMPRESION_GF;
        $respuesta[$index]['gf_impresion'] = $row->GF_IMPRESION;
        $respuesta[$index]['preparacion_gf'] = $row->PREPARACION_GF;
        $respuesta[$index]['gf_preparacion'] = $row->GF_PREPARACION;
        $respuesta[$index]['instalacion_gf'] = $row->INSTALACION_GF;
        $respuesta[$index]['gf_instalacion'] = $row->GF_INSTALACION;
        $respuesta[$index]['entrega_gf'] = $row->ENTREGA_GF;
        $respuesta[$index]['gf_entrega'] = $row->GF_ENTREGA;
        $respuesta[$index]['maquila_gf'] = $row->MAQUILAS_GF;
        $respuesta[$index]['gf_maquila'] = $row->GF_MAQUILAS;
        
        $respuesta[$index]['empresa'] = $row->EMPRESA;
        

        if($respuesta[$index]['fecha_programacion']  == ''){
            $fecha_validar = strtotime($respuesta[$index]['fecha_entrega']);
            $respuesta[$index]['dia'] =  (int)date("N", strtotime($respuesta[$index]['fecha_entrega']));
            $respuesta[$index]['programado'] = 0;
        }else
        {
            $fecha_validar = strtotime($respuesta[$index]['fecha_programacion']);
            $respuesta[$index]['dia'] =  (int)date("N", strtotime($respuesta[$index]['fecha_programacion']));
            $respuesta[$index]['programado'] = 1;   
        }

        $respuesta[$index]['fecha_format'] = date("Y-m-d H:i", $fecha_validar);
        $respuesta[$index]['fecha_format'] = str_replace([' '], 'T', $respuesta[$index]['fecha_format']);

        $respuesta[$index]['semana_actual'] =  0;
        //$respuesta[$index]['semana_actual'] =$respuesta[$index]['fecha_entrega'];
        if(date("W",$fecha_validar) == $semana_actual)
            $respuesta[$index]['semana_actual'] =  1;
        else
            $respuesta[$index]['semana_actual'] =  0;
        
    }
    //print_r($respuesta);

    

    echo json_encode($respuesta);

?>
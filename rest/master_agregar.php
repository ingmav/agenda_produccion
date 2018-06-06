<?php
header('Content-type: application/json; charset=utf-8');
$usuario = "root";
$contrasena = "";
try {
    $conexion = new PDO('mysql:host=localhost;dbname=master_cliente', $usuario, $contrasena);
} catch (PDOException $e) {
    echo 'Connection failed'.$e->getMessage();
}

$json = file_get_contents('php://input');
$data=json_decode($json);

if($data->accion == "ver")
{
    try {

        $conexion->beginTransaction(); 
        $sql = "select id, descripcion from empresa where id =".$data->id." and deleted_at is null";
        $rel = $conexion->prepare($sql);
        $rel->execute();
        $empresa = [];
        while ($result = $rel->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {    
            $index = count($empresa);
            $empresa[$index]['id'] = $result[0];     
            $empresa[$index]['empresa'] = $result[1];     
           
            $sql_r = "select id, id_empresa, alias, razon_social, direccion from razon_social where id_empresa =".$empresa[$index]['id']." and deleted_at is null";
            $rel_r = $conexion->prepare($sql_r);
            $rel_r->execute();
            $razon = [];
            while ($result_r = $rel_r->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {    
                $index_r = count($razon);
                $razon[$index_r]['id'] = $result_r[0];    
                $razon[$index_r]['id_empresa'] = $result_r[1];     
                $razon[$index_r]['alias'] = $result_r[2];     
                $razon[$index_r]['razon_social'] = $result_r[3];     
                $razon[$index_r]['direccion'] = $result_r[4];     
            }
            $empresa[$index]['razon'] = $razon;

            $sql_p = "select pago.id, pago.id_razon_social, pago.id_metodo_pago, pago.politica_pago, razon_social.alias 
            from pago, razon_social where pago.id_razon_social=razon_social.id and razon_social.id_empresa='".$empresa[$index]['id']."' 
            and pago.deleted_at is null and razon_social.deleted_at is null";
            $rel_p = $conexion->prepare($sql_p);
            $rel_p->execute();
            $pago = [];
            while ($result_p = $rel_p->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {    
                $index_p = count($pago);
                $pago[$index_p]['id'] = $result_p[0];         
                $pago[$index_p]['id_razon_social'] = (int)$result_p[1];     
                $pago[$index_p]['id_metodo_pago'] = (int)$result_p[2];     
                $pago[$index_p]['politica_pago'] = $result_p[3];     
                $pago[$index_p]['razon_social'] = $result_p[4];     
            }
            $empresa[$index]['pago'] = $pago;

            $sql = "select id, id_empresa, link, usuario, contrasena from pagina_web where id_empresa =".$empresa[$index]['id']." and deleted_at is null";
            $rel = $conexion->prepare($sql);
            $rel->execute();
            $pagina = [];
            while ($result = $rel->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {    
                $index_extra = count($pagina);
                $pagina[$index_extra]['id'] = $result[0];    
                $pagina[$index_extra]['id_empresa'] = $result[1];     
                $pagina[$index_extra]['link'] = $result[2];     
                $pagina[$index_extra]['usuario'] = $result[3];     
                $pagina[$index_extra]['contrasena'] = $result[4];     
            }
            $empresa[$index]['pagina'] = $pagina;

            $sql = "select 
            contacto.id, 
            contacto.id_empresa, 
            contacto.id_razon_social, 
            contacto.nombre, 
            contacto.puesto, 
            contacto.telefono_fijo, 
            contacto.celular, 
            contacto.correo, 
            contacto.correo_alterno, 
            contacto.id_cobranza,
            razon_social.alias 
            from contacto, razon_social 
            where
            contacto.id_razon_social=razon_social.id 
            and contacto.id_empresa =".$empresa[$index]['id']." and contacto.deleted_at is null and razon_social.deleted_at is null order by contacto.id_razon_social";
            $rel = $conexion->prepare($sql);
            $rel->execute();
            $contacto = [];
            while ($result = $rel->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {    
                $index_extra = count($contacto);
                //print_r($result);
                $contacto[$index_extra]['id'] = $result[0];    
                $contacto[$index_extra]['id_empresa'] = $result[1];     
                $contacto[$index_extra]['id_razon_social'] = $result[2];     
                $contacto[$index_extra]['nombre'] = utf8_encode($result[3]);     
                $contacto[$index_extra]['puesto'] = utf8_encode($result[4]);     
                $contacto[$index_extra]['telefono_fijo'] = $result[5];     
                $contacto[$index_extra]['celular'] = $result[6];     
                $contacto[$index_extra]['correo'] = $result[7];     
                $contacto[$index_extra]['correo_alterno'] = $result[8];     
                $contacto[$index_extra]['id_cobranza'] = $result[9];     
                $contacto[$index_extra]['razon'] = $result[10];     
            }
            $empresa[$index]['contacto'] = $contacto;

            $sql = "select 
            proceso_pago.id, 
            proceso_pago.id_empresa, 
            proceso_pago.titulo, 
            proceso_pago.descripcion
            from proceso_pago, empresa 
            where
            proceso_pago.id_empresa=empresa.id
            and proceso_pago.id_empresa =".$empresa[$index]['id']." and proceso_pago.deleted_at is null order by proceso_pago.id";
            $rel = $conexion->prepare($sql);
            $rel->execute();
            $proceso = [];
            while ($result = $rel->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {    
                $index_extra = count($proceso);
                $proceso[$index_extra]['id'] = $result[0];    
                $proceso[$index_extra]['id_empresa'] = $result[1];     
                $proceso[$index_extra]['titulo'] = utf8_encode($result[2]);     
                $proceso[$index_extra]['descripcion'] = utf8_encode($result[3]);     
                
            }
            $empresa[$index]['procedimiento_pago'] = $proceso;

            $sql = "select 
            id, 
            id_empresa, 
            id_tipo_precio, 
            nombre_articulo,
            precio_x_m2,
            observaciones
            from precio_especial 
            where
            id_empresa =".$empresa[$index]['id']." and deleted_at is null order by id";
            $rel = $conexion->prepare($sql);
            $rel->execute();
            $precio_articulos = [];
            $precio_servicio = [];
            while ($result = $rel->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {    
                $index_precio1 = count($precio_articulos);
                $index_precio2 = count($precio_servicio);
                if($result[2] == 1)
                {
                    $precio_articulos[$index_precio1]['id'] = $result[0];    
                    $precio_articulos[$index_precio1]['id_empresa'] = $result[1];     
                    $precio_articulos[$index_precio1]['id_tipo'] = $result[2];     
                    $precio_articulos[$index_precio1]['nombre_articulo'] = utf8_encode($result[3]);     
                    $precio_articulos[$index_precio1]['precio_x_m2'] = utf8_encode($result[4]);     
                    $precio_articulos[$index_precio1]['observaciones'] = utf8_encode($result[5]);         
                }else if($result[2] == 2)
                {
                    $precio_servicio[$index_precio2]['id'] = $result[0];    
                    $precio_servicio[$index_precio2]['id_empresa'] = $result[1];     
                    $precio_servicio[$index_precio2]['id_tipo'] = $result[2];  
                    $precio_servicio[$index_precio2]['nombre_articulo'] = utf8_encode($result[3]);        
                    $precio_servicio[$index_precio2]['precio_x_m2'] = 0;     
                    $precio_servicio[$index_precio2]['observaciones'] = utf8_encode($result[5]);         
                }
                
            }
            $empresa[$index]['precio_especial']['articulos'] = $precio_articulos;
            $empresa[$index]['precio_especial']['servicios'] = $precio_servicio;
        }
        $conexion->commit();
    }catch (PDOException $e) {
        echo 'Connection failed'.$e->getMessage();
        $conexion->rollback();
    }
    echo json_encode($empresa);
}

if($data->accion == "agregar_empresa")
{
    try {

        $conexion->beginTransaction(); 
        $sql = "insert into empresa(descripcion) values('".strtoupper($data->empresa)."')";
        $rel = $conexion->prepare($sql);
        $rel->execute();
        $data->id = $conexion->lastInsertId();
        $conexion->commit();
    }catch (PDOException $e) {
        echo 'Connection failed'.$e->getMessage();
        $conexion->rollback();
    }
    echo json_encode($data);
}

if($data->accion == "razon")
{
    try {

        $conexion->beginTransaction(); 
        if($data->id > 0)
        {
            $sql_razon = "update razon_social set alias='".strtoupper($data->alias)."', razon_social='".strtoupper($data->razon)."', direccion='".strtoupper($data->direccion)."' where id_empresa=".$data->empresa." and id=".$data->id;
            $rel_razon = $conexion->prepare($sql_razon);
            $rel_razon->execute();
        }else{
            $sql_razon = "insert into razon_social(id_empresa, alias, razon_social, direccion) values(".$data->empresa.", '".strtoupper($data->alias)."', '".strtoupper($data->razon)."', '".strtoupper($data->direccion)."' )";
            $rel_razon = $conexion->prepare($sql_razon);
            $rel_razon->execute();
        }
        $data->id = $conexion->lastInsertId();
        //$data->id = 12;

        unset($data->accion);
        $conexion->commit();
    }catch (PDOException $e) {
        echo 'Connection failed'.$e->getMessage();
        $conexion->rollback();
    }
    echo json_encode($data);
}

if($data->accion == "metodo")
{
    try {
        
        $conexion->beginTransaction(); 
        if($data->id > 0)
            $sql = "update pago set id_razon_social='".$data->id_razon_social->id."', id_metodo_pago='".$data->id_metodo_pago->id."', politica_pago='".strtoupper($data->politica_pago)."' where id=".$data->id;
        else
            $sql = "insert into pago(id_razon_social, id_metodo_pago, politica_pago) values(".$data->id_razon_social->id.", '".$data->id_metodo_pago->id."', '".strtoupper($data->politica_pago)."' )";
        
        $rel = $conexion->prepare($sql);
        $rel->execute();
        $data->id = $conexion->lastInsertId();

        unset($data->accion);
        $conexion->commit();
        
    }catch (PDOException $e) {
        echo 'Connection failed'.$e->getMessage();
        $conexion->rollback();
    }
    echo json_encode($data);
}

if($data->accion == "pagina")
{
    try {

        $conexion->beginTransaction(); 
        if((int)$data->id > 0)
        {
            $sql = "update pagina_web set link='".strtoupper($data->link)."', usuario='".$data->usuario."', contrasena='".$data->contrasena."' where and id=".$data->id;
        }else{
            $sql = "insert into pagina_web(id_empresa, link, usuario, contrasena) values(".$data->empresa.", '".strtoupper($data->link)."', '".$data->usuario."', '".$data->contrasena."' )";
        }
        $rel = $conexion->prepare($sql);
        $rel->execute();
        $data->id = $conexion->lastInsertId();
        //$data->id = 12;

        unset($data->accion);
        $conexion->commit();
    }catch (PDOException $e) {
        echo 'Connection failed'.$e->getMessage();
        $conexion->rollback();
    }
    echo json_encode($data);
}

if($data->accion == "contacto")
{
    try {
        
        $conexion->beginTransaction(); 
        if($data->id > 0)
            $sql = "update contacto set id_razon_social='".$data->id_razon_social->id."', nombre='".strtoupper(utf8_decode($data->nombre))."', puesto='".strtoupper(utf8_decode($data->puesto))."', telefono_fijo='".strtoupper($data->telefono_fijo)."', celular='".strtoupper($data->celular)."', correo='".strtoupper($data->correo)."', correo_alterno='".strtoupper($data->correo_alterno)."', id_cobranza='".$data->id_cobranza->id."' where id=".$data->id;
        else
            $sql = "insert into contacto(id_empresa, id_razon_social, nombre, puesto, telefono_fijo, celular, correo, correo_alterno, id_cobranza) values(".$data->empresa.",".$data->id_razon_social->id.", '".strtoupper(utf8_decode($data->nombre))."', '".strtoupper(utf8_decode($data->puesto))."', '".$data->telefono_fijo."', '".$data->celular."', '".strtoupper($data->correo)."', '".strtoupper($data->correo_alterno)."', '".$data->id_cobranza->id."' )";
        
        $rel = $conexion->prepare($sql);
        $rel->execute();
        $data->id = $conexion->lastInsertId();

        unset($data->accion);
        $conexion->commit();
        
    }catch (PDOException $e) {
        echo 'Connection failed'.$e->getMessage();
        $conexion->rollback();
    }
    echo json_encode($data);
}

if($data->accion == "procedimiento")
{
    try {

        $conexion->beginTransaction(); 
        $data->titulo = utf8_decode($data->titulo);
        $data->descripcion = utf8_decode($data->descripcion);
        
        if((int)$data->id > 0)
        {
            $sql = "update proceso_pago set titulo='".strtoupper($data->titulo)."', descripcion='".strtoupper($data->descripcion)."' where and id=".$data->id;
        }else{
            $sql = "insert into proceso_pago(id_empresa, titulo, descripcion) values(".$data->empresa.", '".strtoupper($data->titulo)."', '".strtoupper($data->descripcion)."')";
        }
        $rel = $conexion->prepare($sql);
        $rel->execute();
        $data->id = $conexion->lastInsertId();
        //$data->id = 12;

        unset($data->accion);
        $conexion->commit();
    }catch (PDOException $e) {
        echo 'Connection failed'.$e->getMessage();
        $conexion->rollback();
    }
    echo json_encode($data);
}

if($data->accion == "precio")
{
    try {
        
        $conexion->beginTransaction(); 
        $data->nombre_articulo = utf8_decode($data->nombre_articulo);
        $data->observaciones = utf8_decode($data->observaciones);
        
        if((int)$data->id > 0)
        {
            $sql = "update precio_especial set nombre_articulo='".strtoupper($data->nombre_articulo)."', precio_x_m2=".$data->precio_x_m2.", observaciones='".strtoupper($data->observaciones)."' where id=".$data->id;
        }else{
            $sql = "insert into precio_especial(id_empresa, id_tipo_precio, nombre_articulo, precio_x_m2, observaciones) values(".$data->empresa.", ".$data->id_tipo.", '".strtoupper($data->nombre_articulo)."', ".$data->precio_x_m2.", '".strtoupper($data->observaciones)."')";
        }
    
        $rel = $conexion->prepare($sql);
        $rel->execute();
        $data->id = $conexion->lastInsertId();
        //$data->id = 12;

        unset($data->accion);
        $conexion->commit();
    }catch (PDOException $e) {
        echo 'Connection failed'.$e->getMessage();
        $conexion->rollback();
    }
    echo json_encode($data);
}

if($data->accion == "eliminar")
{
    $conexion->beginTransaction(); 
    if($data->index == 1)
    {
        $sql = "update razon_social set deleted_at='".date("Y-m-d H:i:s")."' where id_empresa=".$data->empresa." and id=".$data->id;
    }else if($data->index == 2)
    {
        $sql = "update pago set deleted_at='".date("Y-m-d H:i:s")."' where id=".$data->id;
    }else if($data->index == 3)
    {
        $sql = "update pagina_web set deleted_at='".date("Y-m-d H:i:s")."' where id=".$data->id;
    }else if($data->index == 4)
    {
        $sql = "update contacto set deleted_at='".date("Y-m-d H:i:s")."' where id=".$data->id;
    }else if($data->index == 5)
    {
        $sql = "update proceso_pago set deleted_at='".date("Y-m-d H:i:s")."' where id=".$data->id;
    }
    else if($data->index == 6)
    {
        $sql = "update precio_especial set deleted_at='".date("Y-m-d H:i:s")."' where id=".$data->id;
    }
    $rel = $conexion->prepare($sql);
    $rel->execute();

    unset($data->accion);
    $conexion->commit();
    echo json_encode($data);
}
?>    
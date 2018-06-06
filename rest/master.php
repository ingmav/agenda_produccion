<?php
header('Content-type: application/json; charset=utf-8');
$usuario = "root";
$contrasena = "";
try {
    $conexion = new PDO('mysql:host=localhost;dbname=master_cliente', $usuario, $contrasena);
} catch (PDOException $e) {
    echo 'Connection failed'.$e->getMessage();
}

try {
    $sql_search = "SELECT e.id, e.descripcion FROM empresa e";
    $statement = $conexion->prepare($sql_search);
    $statement->execute();
    $respuesta = [];
    while ($result = $statement->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
        $index = count($respuesta);
        $respuesta[$index]['id'] = $result[0];     
        $respuesta[$index]['empresa'] = $result[1]; 
        
        $sql_razon = "SELECT r.id, r.alias, r.razon_social, r.direccion, c.nombre, c.telefono_fijo, c.celular, mp.descripcion, p.politica_pago FROM razon_social r 
        left join contacto c on c.id_empresa=r.id_empresa and c.id_razon_social=r.id and c.id_cobranza=1 and c.deleted_at is null
        left join pago p on p.id_razon_social=r.id and p.deleted_at is null
        left join metodo_pago mp on mp.id=p.id_metodo_pago and mp.deleted_at is null
        where r.id_empresa=".(int)$result[0]." and r.deleted_at is null";
        $rel_razon = $conexion->prepare($sql_razon);
        $rel_razon->execute();
        $razon = [];
        while ($result_razon = $rel_razon->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {    
            $index_razon = count($razon);
            $razon[$index_razon]['id'] = $result_razon[0];     
            $razon[$index_razon]['alias'] = strtoupper(utf8_decode($result_razon[1]));     
            $razon[$index_razon]['razon_social'] = strtoupper(utf8_decode($result_razon[2])); 
            $razon[$index_razon]['direccion'] = strtoupper(utf8_decode($result_razon[3])); 
            $razon[$index_razon]['contacto'] = strtoupper(utf8_decode($result_razon[4])); 
            $razon[$index_razon]['telefono'] = $result_razon[5]; 
            $razon[$index_razon]['celular'] = $result_razon[6]; 
            $razon[$index_razon]['metodo'] = strtoupper($result_razon[7]); 
            $razon[$index_razon]['politica'] = strtoupper(utf8_decode($result_razon[8])); 
        }
        $respuesta[$index]['razon'] = $razon;
        

    }
}catch (PDOException $e) {
    echo 'Connection failed'.$e->getMessage();
}
    //$respuesta[0] = ["1"];
    echo json_encode($respuesta);
?>
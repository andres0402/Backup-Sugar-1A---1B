<?php
    if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

    global $db;
    $id_linea_pto = $_POST['id_linea_pto'];

    $query = "SELECT c.id FROM c_contratos c INNER JOIN c_contratos_l_lineas_presupuesto_c x 
    ON c.id = x.c_contratos_l_lineas_presupuestoc_contratos_ida
    WHERE x.c_contratos_l_lineas_presupuestol_lineas_presupuesto_idb = '$id_linea_pto'";

    $id_contrato = "";
    $result = $db->query($query,true);
    while ($row = $db->fetchByAssoc($result)) {
        $id_contrato = $row['id'];
    }

    if($id_contrato != ""){
        $query = "SELECT fecha_finalizacion AS fecha_fin 
        FROM c_contratos
        WHERE id = '$id_contrato'";
    
        $result = $db->query($query,true);
        while ($row = $db->fetchByAssoc($result)) {
            echo $row['fecha_fin'];
        }
    
        echo "";
    }
    else{
        echo "";
    }

?>

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
        $query = "SELECT valor_actual_cop AS valor_actual FROM b_bolsas b INNER JOIN c_contratos_b_bolsas_1_c x 
        ON b.id = x.c_contratos_b_bolsas_1b_bolsas_idb
        WHERE x.c_contratos_b_bolsas_1c_contratos_ida = '$id_contrato'";
    
        $total_bolsas = 0;
        $result = $db->query($query,true);
        while ($row = $db->fetchByAssoc($result)) {
            $total_bolsas += $row['valor_actual'];
        }
    
        echo $total_bolsas;
    }
    else{
        echo -1;
    }

?>

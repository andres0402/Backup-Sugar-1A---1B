<?php
    if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

    global $db;
    $id_linea_pto = $_POST['id_linea_pto'];

    $query = "SELECT valor_actual_cop_c AS valor_actual 
    FROM b_bolsas_cstm b INNER JOIN b_bolsas_l_lineas_presupuesto_c x ON b.id_c = x.b_bolsas_l_lineas_presupuestob_bolsas_ida
    WHERE x.b_bolsas_l_lineas_presupuestol_lineas_presupuesto_idb = '$id_linea_pto' AND x.deleted = 0";

    $valor_bolsa = "";
    $result = $db->query($query,true);
    while ($row = $db->fetchByAssoc($result)) {
        $valor_bolsa = $row['valor_actual'];
    }

    if($valor_bolsa != ""){
        echo $valor_bolsa;
    }
    else{
        echo -1;
    }

?>

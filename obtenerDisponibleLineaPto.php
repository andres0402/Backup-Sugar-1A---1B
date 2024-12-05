<?php
    if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

    global $db;
    $id_linea_pto = $_POST['id_linea_pto'];

    $query = "SELECT valor_disponible_c AS disponible 
    FROM l_lineas_presupuesto_cstm 
    WHERE id_c = '$id_linea_pto'";

    $result = $db->query($query,true);
    while ($row = $db->fetchByAssoc($result)) {
        echo $row['disponible'];
    }

    echo 0;

?>


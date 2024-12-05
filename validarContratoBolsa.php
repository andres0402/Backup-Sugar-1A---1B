<?php
    if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

    global $db;
    $id_bolsa = $_POST['id_bolsa'];


    $query = "SELECT c_contratos_b_bolsas_1c_contratos_ida AS id_contrato
    FROM c_contratos_b_bolsas_1_c 
    WHERE c_contratos_b_bolsas_1b_bolsas_idb = '$id_bolsa'";

    $result = $db->query($query,true);
    while ($row = $db->fetchByAssoc($result)) {
        echo $row['id_contrato'];
    }

    echo "";

?>


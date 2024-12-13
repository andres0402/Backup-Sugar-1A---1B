<?php
require_once('include/MVC/View/views/view.edit.php');

class l_pagosViewEditViewPedido extends ViewEdit {
    function __construct() {
        parent::__construct();
    }

    function preDisplay() {
        parent::preDisplay();
        
        $this->bean->valor_cop = '0.00';
        $this->bean->valor_usd = '0.00';
        $this->bean->iva_c = '0.00';
        $this->bean->fecha_he_c = date("Y-m-d");


        
        $metadataFile = 'custom/modules/l_pagos/metadata/editviewdefs1.php';

        
        // Configura la vista EditViewPedido
        $this->ev->view = 'EditViewPedido';
        //var_dump($this->module);
        
        $tplFile = get_custom_file_if_exists('include/EditView/EditView.tpl');
        
        $this->ev->setup($this->module, $this->bean, $metadataFile, $tplFile);
    }

    // Muestra la vista personalizada
    function display() { 
        if (isset($_GET['idPedido'])) {
            $idPedido = $_GET['idPedido'];
            //Se obtienen todos los ids presupuestales del pedido
            $query = "SELECT i.id AS id_ID, i.name AS numero_id
                FROM   l_prepedidos i INNER JOIN f_pedidos_l_prepedidos_1_c x 
                       ON i.id = x.f_pedidos_l_prepedidos_1l_prepedidos_idb
                WHERE  x.f_pedidos_l_prepedidos_1f_pedidos_ida = '$idPedido' 
                       AND i.deleted = 0 AND x.deleted = 0";
            $results = $this->bean->db->query($query, true);
            $originalBean = $this->bean;
            echo "<h3>Por favor registre las provisiones correspondientes para cada ID presupuestal del pedido: <h3/>";
            echo '<div id="ids-container">';
            echo '<form action="index.php?module=l_pagos&action=EditViewPedido&idPedido='. $idPedido .'" method="POST" name="guardarIds" id="guardarIds">';
            $formCount = 0;
            while ($row = $this->bean->db->fetchByAssoc($results)) {
                $numero_id = $row['numero_id'];
                $id_ID = $row['id_ID'];

                $query = "SELECT l.rubro AS rubro 
                FROM l_lineas_presupuesto l INNER JOIN l_lineas_presupuesto_l_prepedidos_c x 
                ON l.id = x.l_lineas_presupuesto_l_prepedidosl_lineas_presupuesto_ida 
                WHERE x.l_lineas_presupuesto_l_prepedidosl_prepedidos_idb = '$id_ID'";

                $result = $this->bean->db->query($query, true);
                $res = $this->bean->db->fetchByAssoc($result);

                $rubro = $res['rubro'];

                //Sólo se muestran los ids que sean susceptibles de provisión (OPEX)
                if ($rubro == 'OPEX'){
                    echo '<div id="EditViewId_'. $formCount .'">';
                    echo '<h3>ID presupuestal número ' . $numero_id . ':</h3>';
                    echo '<input type="hidden" name="id_presupuestal[]" value="' . $numero_id . '">';
                    echo '<input type="hidden" name="id_presupuestal_id[]" value="' . $id_ID . '">';
                    
                    parent::display();
                    
                    echo '</div>';
                    $formCount++;
                }
                
            }

            if ($formCount > 0){
                echo '<input type="submit" value="Guardar" name="button" class="button" id="SAVE" onclick="'. $this->guardarHojasPedido() .'">';
                echo '<input type="button" value="Cancelar" name="button" class="button" id="CANCEL" onclick="window.close();">';
            }
            else{
                echo "<p>El pedido no tiene IDs presupuestales registrados susceptibles de provisión (OPEX)<p/><br/>";
                echo '<input type="button" value="Cerrar" name="button" class="button" id="CANCEL" onclick="window.close();">';
            }
            echo "</form>";
            echo '</div>';

            echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                var forms = document.querySelectorAll("div[id^=EditViewId_]");
                
                forms.forEach(function(form, index) {
                    var dateInputs = form.querySelectorAll("input[name=fecha_he_c]");
                    var valorCopInputs = form.querySelectorAll("input[name=valor_cop]");
                    var valorUsdInputs = form.querySelectorAll("input[name=valor_usd]");
                    var valorIvaInputs = form.querySelectorAll("input[name=iva_c]");
                    var triggers = form.querySelectorAll("img[id^=fecha_he_c_trigger]");
                    
                    // Cambia el ID de cada input y botón de forma dinámica
                    valorCopInputs.forEach(function(input, inputIndex) {
                        input.id = "valor_cop_" + index;
                        input.name = "valor_cop[]";
                    });

                    valorUsdInputs.forEach(function(input, inputIndex) {
                        input.id = "valor_usd_" + index;
                        input.name = "valor_usd[]";
                    });

                    valorIvaInputs.forEach(function(input, inputIndex) {
                        input.id = "iva_c_" + index;
                        input.name = "iva_c[]";
                    });

                    dateInputs.forEach(function(input, inputIndex) {
                        input.id = "fecha_he_c_" + index;
                        input.name = "fecha_he_c[]";
                    });

                    triggers.forEach(function(trigger, triggerIndex) {
                        trigger.id = "fecha_he_c_trigger_" + index;
                    });

                    // Reconfigura los date pickers con los nuevos IDs
                    if (typeof Calendar !== "undefined" && Calendar.setup) {
                        Calendar.setup({
                            inputField: "fecha_he_c_" + index,
                            ifFormat: "%Y-%m-%d",
                            daFormat : "%Y-%m-%d %H:%M",
                            dateStr : "",
                            startWeekday: 0,
                            button: "fecha_he_c_trigger_" + index,
                            singleClick: true,
                            step: 1,
                            weekNumbers:false
                        });
                    }
                });
            });
        </script>';
        } else {
            echo "No se recibió ningún ID de pedido.";
        }
        $this->bean = $originalBean;
        
    
}

//Función que guarda las hojas de entrada para cada id presupuestal en la base de datos
function guardarHojasPedido(){
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recoge los valores de todos los inputs
        $fechas = $_POST['fecha_he_c'];
        $numeros_ids = $_POST['id_presupuestal'];
        $ids_ids = $_POST['id_presupuestal_id'];
        $valores_cop = $_POST['valor_cop'];
        $valores_usd = $_POST['valor_usd'];
        $valores_iva = $_POST['iva_c'];

        $v_hoy = date("Y-m-d H:i:s");

        global $current_user;
    
        foreach ($numeros_ids as $index => $numero_id) {

            $query = "SELECT create_id() AS id;";
            $result = $this->bean->db->query($query, true);
            $row = $this->bean->db->fetchByAssoc($result);
            $id_he = $row['id'];

            if ($valores_cop[$index] == ""){
                $valores_cop[$index] = '0.00';
            }

            if ($valores_usd[$index] == ""){
                $valores_usd[$index] = '0.00';
            }

            if ($valores_iva[$index] == ""){
                $valores_iva[$index] = '0.00';
            }

            if ($fechas[$index] == ""){
                $fechas[$index] = date("Y-m-d");
            }



            $valores_cop[$index] = str_replace(',', '.', $valores_cop[$index]);

            $valores_usd[$index] = str_replace(',', '.', $valores_usd[$index]);

            $valores_iva[$index] = str_replace(',', '.', $valores_iva[$index]);


            $query = "SELECT count(*) AS cuenta FROM l_pagos_cstm p INNER JOIN l_prepedidos_l_pagos_1_c x ON p.id_c = x.l_prepedidos_l_pagos_1l_pagos_idb 
            WHERE x.l_prepedidos_l_pagos_1l_prepedidos_ida = '$ids_ids[$index]' AND p.tipo_c = 'Provision' AND x.deleted = 0";

            $result = $this->bean->db->query($query, true);
            $res = $this->bean->db->fetchByAssoc($result);

            $cuenta = $res['cuenta'];

            $numero_provision = $cuenta + 1;

            $query = "SELECT assigned_user_id FROM l_prepedidos WHERE id = '$ids_ids[$index]'";

            $result = $this->bean->db->query($query, true);
            $res = $this->bean->db->fetchByAssoc($result);

            $usuario_asignado = $res['assigned_user_id'];

            $query = "INSERT INTO l_pagos (id, name, date_entered, date_modified, modified_user_id, created_by, deleted, assigned_user_id, valor_cop, valor_usd) VALUES ('$id_he', '$numero_id". "_PR" . $numero_provision ."', '$v_hoy', '$v_hoy', '$usuario_asignado', '$usuario_asignado', 0, '$usuario_asignado', ". $valores_cop[$index] .", ". $valores_usd[$index] .");";
            $this->bean->db->query($query, true);

            $query = "INSERT INTO l_pagos_cstm (id_c, tipo_c, iva_c, genera_factura_c, fecha_he_c) VALUES ('$id_he', 'Provision', ". $valores_iva[$index] .", 1, '$fechas[$index]');";
            $this->bean->db->query($query, true);

            $query = "INSERT INTO l_prepedidos_l_pagos_1_c (id, date_modified, deleted, l_prepedidos_l_pagos_1l_prepedidos_ida, l_prepedidos_l_pagos_1l_pagos_idb) VALUES (create_id(), '$v_hoy', 0, '$ids_ids[$index]', '$id_he');";
            $this->bean->db->query($query, true);

            $query = "SELECT sum(valor_cop) AS valor_provisiones 
            FROM l_pagos p INNER JOIN l_prepedidos_l_pagos_1_c x ON  p.id = x.l_prepedidos_l_pagos_1l_pagos_idb, l_pagos_cstm cs
            WHERE x.l_prepedidos_l_pagos_1l_prepedidos_ida = '$ids_ids[$index]' AND x.deleted = 0 AND cs.tipo_c = 'Provision' AND cs.id_c = p.id";

            $valor_provisiones = 0;

            $result = $this->bean->db->query($query, true);
            $res = $this->bean->db->fetchByAssoc($result);

            $valor_provisiones = $res['valor_provisiones'];

            $valor_total = $valor_provisiones + $valores_cop[$index];

            $query = "UPDATE l_prepedidos_cstm cs SET cs.provision_cop_c = $valor_total WHERE cs.id_c = '$ids_ids[$index]'";
            $this->bean->db->query($query, true);
        }

        echo "<script type='text/javascript'>
        window.open('index.php?module=l_pagos&action=ViewPedidoSuccess', 'nombreVentana', 'width=800,height=600');
        </script>";
    }
    

}

    
}
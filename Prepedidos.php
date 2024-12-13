<?php

//Por Jaime Orjuela 2016-05-09 13:34
//Editado por Santiago Velastegui 2024-08-30 08:40
require_once('include/SugarPHPMailer.php'); 
require_once('include/utils/db_utils.php');

class Prepedidos {
    function initialStat($bean, $event, $arguments) {
		//Para evitar valores espúreos en las hojas de entrada, se borran las siguientes tres variables: 
         $_SESSION['valor_he_cop'] = 0;
         $_SESSION['valor_he_usd'] = 0;
         $_SESSION['valor_he_trm'] = 0;	
	
        //Se guardan los estados iniciales en variables de session
	    $_SESSION['v_IdPto_assigned_user_id'] = $bean->assigned_user_id;
	    $_SESSION['v_IdPto_estado'] = $bean->estado;
	    $_SESSION['v_IdPto'] = $bean->id;
	    $_SESSION['v_valor_id_cop'] = $bean->valor_id_cop;

		// $GLOBALS['log']->error("VALIDAXX6: $date_entered  $date_modified id_prepedido: $id_prepedido - creado_por: $user_id - valor_id_cop: $valor_id_cop - valor_id_usd: $valor_id_usd - trm_gp: $trm_gp - mes_causacion: $mes_causacion - linea_pto_name: $linea_pto_name - linea_pto_id: $linea_pto_id - Presupuesto Total: $pto_total - Presupuesto Consumido: $pto_consumido - Flujo Presupuesto mes: $flujo_pto_mes - Query: $query");
		
		foreach($lineas_pto as $linea_pto) {
			$_SESSION['v_linea_pto_name'] = $linea_pto->name;
		}		
		
	   if($bean->estado == "Acta_ID_total") {
			$query = "UPDATE l_pagos AS a INNER JOIN l_prepedidos_l_pagos_1_c AS b ON a.id = b.l_prepedidos_l_pagos_1l_pagos_idb SET a.assigned_user_id = '1' WHERE b.l_prepedidos_l_pagos_1l_prepedidos_ida = '" . $bean->id . "'";
			$bean->db->query($query); 
		}
		$_SESSION['module_name'] = "l_prepedidos";

		$lineas_pto = $bean->get_linked_beans('l_lineas_presupuesto_l_prepedidos','l_lineas_presupuesto');
		foreach($lineas_pto as $linea_pto) {
			$pto_total = $linea_pto->valor_total_vigencia_cop + $linea_pto->valor_total_vigencia_usd * $linea_pto->trm_referencia_c;
		}
		// $query = "SELECT sum(IFNULL(p.valor_prepedido_cop,0)) AS pto_consumido " .
		// 				"FROM   l_prepedidos p INNER JOIN l_lineas_presupuesto_l_prepedidos_c lp ON p.id = lp.l_lineas_presupuesto_l_prepedidosl_prepedidos_idb " .
		// 				"       INNER JOIN l_prepedidos_cstm pc ON pc.id_c = p.id " .
		// 				"WHERE  lp.l_lineas_presupuesto_l_prepedidosl_lineas_presupuesto_ida = '". $linea_pto_id . "' AND " .
		// 				"       p.estado NOT IN ('Anulado','Negado','cesta_liberada') AND " .
		// 				"       p.deleted = 0 AND lp.deleted = 0"; 
		// $results = $bean->db->query($query, true);
		// $row = $bean->db->fetchByAssoc($results);
		// $pto_consumido = $row['pto_consumido'];
		$bean->disponible_linea_pto_c = $pto_total;// - $pto_consumido;

		$query = "SELECT sum(valor_cop) AS valor_provisiones 
		FROM l_pagos p INNER JOIN l_prepedidos_l_pagos_1_c x ON  p.id = x.l_prepedidos_l_pagos_1l_pagos_idb, l_pagos_cstm cs
		WHERE x.l_prepedidos_l_pagos_1l_prepedidos_ida = '$bean->id' AND x.deleted = 0 AND cs.tipo_c = 'Provision' AND cs.id_c = p.id";

		$results = $bean->db->query($query, true);
		$row = $bean->db->fetchByAssoc($results);
		$valor_provisiones = $row['valor_provisiones'];

		$bean->provision_cop_c = $valor_provisiones;

	}
	
    function preSave($bean, $event, $arguments) {

		// $GLOBALS['log']->error("VALIDAX0: $date_entered  $date_modified id_prepedido: $id_prepedido - creado_por: $user_id - valor_id_cop: $valor_id_cop - valor_id_usd: $valor_id_usd - trm_gp: $trm_gp - mes_causacion: $mes_causacion - linea_pto_name: $linea_pto_name - linea_pto_id: $linea_pto_id - Presupuesto Total: $pto_total - Presupuesto Consumido: $pto_consumido - Flujo Presupuesto mes: $flujo_pto_mes - Query: $query");

		//Comparando la fecha de creación con la de actualización se determina si se trata de una inserción de registro o de una actualización.
		$date_entered = $bean->date_entered;
		$date_modified = $bean->date_modified;
		$bean->pre_estado_c = $bean->estado;
        
		//Traer la información del prepedido
		$id = $bean->id;
		$id_prepedido = $bean->name;
		$user_id = $bean->created_by;
		$valor_id_cop = $bean->valor_prepedido_cop;
		$valor_id_usd = $bean->valor_prepedido_usd;
		$mes_causacion = $bean->mes_causacion_c;
		$trm_gp = $bean->trm_gp_c;
		$query = "SELECT count(7) AS v_cuenta_nc FROM l_prepedidos WHERE id = '" . $bean->l_prepedido_id_c . "' AND deleted = 0"; 
		$results = $bean->db->query($query, true);
		$row = $bean->db->fetchByAssoc($results);
		$v_cuenta_nc = $row['v_cuenta_nc'];	// Se determina si existe la nota credito	
		
			
		// Traer la información de la línea de presupuesto asociada
		$lineas_pto = $bean->get_linked_beans('l_lineas_presupuesto_l_prepedidos','l_lineas_presupuesto');
		foreach($lineas_pto as $linea_pto) {
			$linea_pto_id = $linea_pto->id;
			$linea_pto_name = $linea_pto->name;
			$linea_pto_desc = $linea_pto->description;
			$linea_pto_rubro = $linea_pto->rubro;
			$pto_total = $linea_pto->valor_total_vigencia_cop + $linea_pto->valor_total_vigencia_usd * $linea_pto->trm_referencia_c;
		}
		$bean->currency_id = $linea_pto_id;
		$bean->id_verificacion_pto = $bean->trm_gp_c;
		
		// Si se cambia la relación de la línea de presupuesto, se inserta la auditoría
		if ($_SESSION['v_linea_pto_name'] <> $linea_pto_name) {
           $auditoria = array();
           $auditoria['field_name'] = 'Líneas de Presupuesto';
           $auditoria['data_type'] = 'relate';
           $auditoria['before'] = $_SESSION['v_linea_pto_name'];
           $auditoria['after'] = $linea_pto_name;
           $bean->db->save_audit_records($bean,$auditoria);		
		}
		
		//$GLOBALS['log']->error("VALIDAXX6: $date_entered  $date_modified id_prepedido: $id_prepedido - creado_por: $user_id - valor_id_cop: $valor_id_cop - valor_id_usd: $valor_id_usd - trm_gp: $trm_gp - mes_causacion: $mes_causacion - linea_pto_name: $linea_pto_name - linea_pto_id: $linea_pto_id - Presupuesto Total: $pto_total - Presupuesto Consumido: $pto_consumido - Flujo Presupuesto mes: $flujo_pto_mes - Query: $query");
		if ($date_entered == $date_modified) {

			if (strlen($linea_pto_id) == 36) {
				// En la siguiente consulta se calcula el valor consumido de la línea de presupuesto asociada al prepedido, esto es:
				// la suma de todos los prepedidos, pedidos, adiciones y demás asociados a la misma línea de presupuesto del prepedido en
				// cuestión.  Sólo se excluyen los que estén en estado negado o anulado.

				// IMPORTANTE:  Para los rubros que estén en USD$ y que por alguna razón el usuario no haya colocado la TRM respectiva, se utiliza
				// la TRM por defecto que se define en la siguiente sentencia SQL. El valor por defecto de la TRM para 2016 está hard-codeado en la 
				// consulta.  Es importante aclarar, que esta situación sólo se podría presentar para los primeros 1000 registros que se ingresaron, 
				// pues aún no se habían implementado reglas a nivel de formulario para evitar que dejaran las TRM vacías... Si está pensando que  
				// la solución era simplemente poner como obligatorio el cam:po... no es así.. pues existe un flujo de proceso y en tres momentos 
				// diferentes se define la TRM, que son cuando se realiza la validación para dar el ID, cuando se compromete y cuando se causa el 
				// rubro, y en cada momento se desconoce la TRM de los momentos posteriores.

				$query = "SELECT sum(IFNULL(p.valor_prepedido_cop,0)) AS pto_consumido " .
						"FROM   l_prepedidos p INNER JOIN l_lineas_presupuesto_l_prepedidos_c lp ON p.id = lp.l_lineas_presupuesto_l_prepedidosl_prepedidos_idb " .
						"       INNER JOIN l_prepedidos_cstm pc ON pc.id_c = p.id " .
						"WHERE  lp.l_lineas_presupuesto_l_prepedidosl_lineas_presupuesto_ida = '". $linea_pto_id . "' AND " .
						"       p.estado NOT IN ('Anulado','Negado','cesta_liberada') AND " .
						"       p.deleted = 0 AND lp.deleted = 0"; 
				$results = $bean->db->query($query, true);
				$row = $bean->db->fetchByAssoc($results);
				$pto_consumido = $row['pto_consumido'];
				$bean->disponible_linea_pto_c = $pto_total - $pto_consumido;
				$bean->disponible_id_cop_c = $bean->valor_prepedido_cop;
				$bean->disponible_id_usd_c = $bean->valor_prepedido_usd;
			
				// Traer la información de la planeación del presupuesto para el mes correspondiente
				if ($mes_causacion > 0) {
					$query = "SELECT t.causacion_planeada_cop_c AS flujo_pto_mes, t.causacion_plan_acum_cop_c - t.causacion_planeada_cop_c AS pto_planeado_meses_ant " .
							"FROM   l_flujos_causacion_cstm t INNER JOIN l_lineas_presupuesto_l_flujos_causacion_c lf ON t.id_c = lf.l_lineas_presupuesto_l_flujos_causacionl_flujos_causacion_idb " .
							"WHERE  lf.l_lineas_presupuesto_l_flujos_causacionl_lineas_presupuesto_ida = '" . $linea_pto_id . "' " .
							"       AND t.mes_c = '" . $mes_causacion . "'";
					$results = $bean->db->query($query, true);
					$row = $bean->db->fetchByAssoc($results);
					$flujo_pto_mes = $row['flujo_pto_mes'];
					$pto_planeado_meses_ant = $row['pto_planeado_meses_ant'];
					$bean->planeado_linea_pto_mes_c = $flujo_pto_mes;
					$bean->pto_sobrante_meses_ant_c = $pto_planeado_meses_ant - $pto_consumido;
					$bean->valor_maximo_mes_c = $flujo_pto_mes + $pto_planeado_meses_ant - $pto_consumido;
		
				}

			}
			
		// $GLOBALS['log']->error("VALIDAXX6: id_prepedido: $id_prepedido - creado_por: $user_id - valor_id_cop: $valor_id_cop - valor_id_usd: $valor_id_usd - trm_gp: $trm_gp - mes_causacion: $mes_causacion - linea_pto_name: $linea_pto_name - linea_pto_id: $linea_pto_id - Presupuesto Total: $pto_total - Presupuesto Consumido: $pto_consumido - Flujo Presupuesto mes: $flujo_pto_mes - Query: $query");
			
		}
		
	  // Se calcula el valor pagado en actas en pesos (COP$) y en dólares (USD$) en el movimiento de registro incluyendo el pago actual
	  if ($linea_pto_rubro <> 'OPEX') {
		  $query = "SELECT IFNULL(SUM((p.valor_cop + p.valor_usd * p.trm) * (1 + pc.iva_c / 100)),0) AS valor_acta_cop_c
					FROM   l_pagos p INNER JOIN l_pagos_cstm pc
						   ON p.id = pc.id_c INNER JOIN l_prepedidos_l_pagos_1_c x
						   ON x.l_prepedidos_l_pagos_1l_pagos_idb = p.id 
					WHERE  pc.tipo_c = 'Hoja_Entrada' AND x.l_prepedidos_l_pagos_1l_prepedidos_ida = '" . $id . "' AND p.deleted = 0 AND x.deleted = 0";
	  } else { 
		  $query = "SELECT IFNULL(SUM(p.valor_cop + p.valor_usd * p.trm),0) AS valor_acta_cop_c
					FROM   l_pagos p INNER JOIN l_pagos_cstm pc
						   ON p.id = pc.id_c INNER JOIN l_prepedidos_l_pagos_1_c x
						   ON x.l_prepedidos_l_pagos_1l_pagos_idb = p.id 
					WHERE  pc.tipo_c = 'Hoja_Entrada' AND x.l_prepedidos_l_pagos_1l_prepedidos_ida = '" . $id . "' AND p.deleted = 0 AND x.deleted = 0";
	  }				
	  $results = $bean->db->query($query, true);
	  $row = $bean->db->fetchByAssoc($results);
	  $bean->valor_acta_cop_c = $row['valor_acta_cop_c'];
      
	  // Se calcula el valor provisionado pesos (COP$) y en dólares (USD$) en el movimiento de registro incluyendo el pago actual
      $query = "SELECT IFNULL(SUM(p.valor_cop + p.valor_usd * p.trm),0) AS provision_cop_c
                FROM   l_pagos p INNER JOIN l_pagos_cstm pc
                	   ON p.id = pc.id_c INNER JOIN l_prepedidos_l_pagos_1_c x
                       ON x.l_prepedidos_l_pagos_1l_pagos_idb = p.id 
                WHERE  pc.tipo_c = 'Provision' AND x.l_prepedidos_l_pagos_1l_prepedidos_ida = '" . $id . "' AND p.deleted = 0 AND x.deleted = 0";
                
      $results = $bean->db->query($query, true);
      $row = $bean->db->fetchByAssoc($results);
      $bean->provision_cop_c = $row['provision_cop_c'];      
      
	  // Se calcula el valor en cestas en pesos (COP$) y en dólares (USD$) en el movimiento de registro incluyendo el pago actual
	  if ($linea_pto_rubro <> 'OPEX') {
		  $query = "SELECT IFNULL(SUM((p.valor_cop + p.valor_usd * p.trm) * (1 + pc.iva_c / 100)),0) AS valor_cesta_cop_c
					FROM   l_pagos p INNER JOIN l_pagos_cstm pc
						   ON p.id = pc.id_c INNER JOIN l_prepedidos_l_pagos_1_c x
						   ON x.l_prepedidos_l_pagos_1l_pagos_idb = p.id 
					WHERE  pc.tipo_c = 'Cesta' AND x.l_prepedidos_l_pagos_1l_prepedidos_ida = '" . $id . "' AND p.deleted = 0 AND x.deleted = 0";
	  } else {
		  $query = "SELECT IFNULL(SUM(p.valor_cop + p.valor_usd * p.trm),0) AS valor_cesta_cop_c
					FROM   l_pagos p INNER JOIN l_pagos_cstm pc
						   ON p.id = pc.id_c INNER JOIN l_prepedidos_l_pagos_1_c x
						   ON x.l_prepedidos_l_pagos_1l_pagos_idb = p.id 
					WHERE  pc.tipo_c = 'Cesta' AND x.l_prepedidos_l_pagos_1l_prepedidos_ida = '" . $id . "' AND p.deleted = 0 AND x.deleted = 0";
	  }	 
	  
	  // Se calcula el valor en notas_credito cestas en pesos (COP$) 
	  $query = "SELECT IFNULL(SUM(p.valor_prepedido_cop),0) AS v_nota_credito
				FROM   l_prepedidos p INNER JOIN l_prepedidos_cstm pc
						ON p.id = pc.id_c
				WHERE  p.id = '" . $bean->l_prepedidos_id_c . "' AND p.deleted = 0 AND pc.solicitado_para_c = 'Nota_Credito'";                
      $results = $bean->db->query($query, true);
      $row = $bean->db->fetchByAssoc($results);
      $v_nota_credito = $row['v_nota_credito'];      
      
	  // $bean->valor_actas_usd_c = $row['valor_actas_usd']; 
      $bean->disponible_id_cop_c = $bean->valor_prepedido_cop - $bean->valor_acta_cop_c - $bean->provision_cop_c - $bean->valor_cesta_cop_c + $v_nota_credito;
	  // $bean->disponible_id_usd_c = $bean->valor_prepedido_usd - $bean->valor_acta_usd_c - $bean->provision_usd_c - $bean->valor_cesta_usd_c;

	  // $GLOBALS['log']->error("VALIDAXX666: id_prepedido: " . $bean->id . " - valor_id_cop: " . $bean->valor_prepedido_cop . " - valor_actas_cop_c: " . $bean->valor_actas_cop_c . " - disponible: " . $bean->disponible_id_cop_c . " - NotaCredito: " . $v_nota_credito);
			
		//Se evalua el estado del prepedido y si está en Acta_ID_total se procede a crear la Nota Crédito.
		$estado = $bean->estado;
		
        if ($estado == "Acta_ID_total") {
			$nota_id = create_guid(); // Id para la nota crédito
			$saldo_cop = 0;
			$saldo_usd = 0;
			
			// Para los cierres de Pedidos se crean Notas Crédito, para los de Procesos de Contratación se crean IDs de Ahorro.
			if ($bean->solicitado_para_c == "Pedido") {
				$solicitado_para = "Nota_Credito";
				// $saldo_cop = $bean->valor_acta_cop_c - $bean->valor_prepedido_cop; 
				// $saldo_usd = $bean->valor_acta_usd_c - $bean->valor_prepedido_usd;
				// $bean->disponible_id_cop_c = 0;
				// $bean->disponible_id_usd_c = 0;
			} elseif ($bean->solicitado_para_c == "Contratacion") {
			    $solicitado_para = "Ahorro";
				// $saldo_cop = $bean->valor_prepedido_cop - $bean->valor_cesta_cop_c; 
				// $saldo_usd = $bean->valor_prepedido_usd - $bean->valor_cesta_usd_c;
				// $bean->disponible_id_cop_c = 0;
				// $bean->disponible_id_usd_c = 0;
			}
			
			if ($bean->disponible_id_cop_c > 1) {
			   //$query = "INSERT INTO l_prepedidos_ids(id_prepedido) VALUES ('" . $nota_id . "')";
			   //$bean->db->query($query); 
			   //$query = "SELECT id_verificacion_pto FROM l_prepedidos_ids WHERE id_prepedido = '" . $nota_id . "'";
               //$results = $bean->db->query($query, true);
			   //$row = $bean->db->fetchByAssoc($results);
			   //$id_verificacion_pto = $row['id_verificacion_pto'];

	  			// $GLOBALS['log']->error("VALIDAXX666: id_prepedido: $id_prepedido - l_prepedidos_id_c: $bean->l_prepedidos_id_c - largo: " . strlen($bean->l_prepedidos_id_c) . " -valor_id_cop: $valor_id_cop - valor_actas_cop_c: $bean->valor_actas_cop_c - disponible_id_cop_c: $bean->disponible_id_cop_c");
			   
			   if ($v_cuenta_nc == 0) { 
				   $query = "INSERT INTO l_prepedidos
						(id,
						 date_entered,
						 date_modified,
						 modified_user_id,
						 created_by,
						 description,
						 deleted,
						 assigned_user_id,
						 estado,
						 fecha_inicio,
						 valor_prepedido_cop,
						 currency_id,
						 valor_prepedido_usd)
					VALUES
						('" . $nota_id . "',
						 date_add(now(), interval 5 hour),
						 date_add(now(), interval 5 hour),
						 '" . $bean->assigned_user_id . "',
						 '" . $bean->assigned_user_id . "',
						 'Nota crédito generada automáticamente por el sistema',
						0,
						'1',
						'Acta_ID_total',
						'" . $bean->fecha_inicio . "',
						-" . $bean->disponible_id_cop_c . ",
						'" . $bean->currency_id . "',
						" . $saldo_usd . ")";
				   $bean->db->query($query); 
				   $query = "INSERT INTO l_prepedidos_cstm
						(id_c,
						 solicitado_para_c,
						 valor_acta_cop_c,
						 valor_acta_usd_c,
						 pre_estado_c,
						 mes_causacion_c,
						 l_prepedidos_id_c)
					VALUES
						('" . $nota_id . "',
						'" . $solicitado_para . "',
						0,
						0,
						'Acta_ID_total',
						'" . $mes_causacion . "',
						'" . $bean->id . "')";
				   $bean->db->query($query);
			   
				   $query = "INSERT INTO l_lineas_presupuesto_l_prepedidos_c
							 (id,
							  date_modified,
							  deleted,
							  l_lineas_presupuesto_l_prepedidosl_lineas_presupuesto_ida,
							  l_lineas_presupuesto_l_prepedidosl_prepedidos_idb)
						   VALUES
							 (create_id(),
							  date_add(now(), interval 5 hour),
							  0,
							  '" . $linea_pto_id . "',
							  '" . $nota_id . "')";
				   $bean->db->query($query);
			   
				   $query = "CALL act_linea_pto_desde_l_prepedidos('" . $linea_pto_id . "')";
				   $bean->db->query($query);

				   $query = "INSERT INTO f_pedidos_l_prepedidos_1_c
								(id,
								date_modified,
								deleted,
								f_pedidos_l_prepedidos_1f_pedidos_ida,
								f_pedidos_l_prepedidos_1l_prepedidos_idb)
							 SELECT
								create_id(),
								date_add(now(), interval 5 hour),
								0,
								f_pedidos_l_prepedidos_1f_pedidos_ida,
								'" . $nota_id . "'
							 FROM f_pedidos_l_prepedidos_1_c
							 WHERE f_pedidos_l_prepedidos_1l_prepedidos_idb = '" . $id . "'";
				   $bean->db->query($query);
			   
			   		// Se actualiza el ID de la nota crédito
			   		$bean->l_prepedidos_id_c = $nota_id;
				   
				} else {
				 
				   $query = "UPDATE l_prepedidos
							SET valor_prepedido_cop = -" . $bean->disponible_id_cop_c  . "
							WHERE id = '" . $bean->l_prepedidos_id_c . "'";
				   $bean->db->query($query);					
				
				}
				
				$bean->disponible_id_cop_c = 0;

			}
			
		}

		//Si el estado es Cesta Liberada se pasa el ID a PMO.
		if ($estado == 'cesta_liberada' || $estado == 'Anulado' || $estado == 'Negado') {
			$auditoria = array();
			$auditoria['field_name'] = 'assigned_user_id';
			$auditoria['data_type'] = 'relate';
			$auditoria['before'] = $_SESSION['v_IdPto_assigned_user_id'];
			$auditoria['after'] = '1';
			$bean->db->save_audit_records($bean,$auditoria);						
			$bean->assigned_user_id = '1';
		}
		
		//Se evalua si se modificó el estado para enviar correo electronico de notificacion.
		$old_estado_prepedido = $_SESSION['v_IdPto_estado'];
        $estado_anterior = $GLOBALS['app_list_strings']['estado_list'][$old_estado_prepedido];
        $estado_nuevo = $GLOBALS['app_list_strings']['estado_list'][$bean->estado];
		
        if (!empty($old_estado_prepedido) && $estado != $old_estado_prepedido) {
           $usuario = new User();
           $usuario->retrieve($bean->modified_user_id);
           $mail = new SugarPHPMailer();
           $mail->From = "pmo_vi@etb.com.co";
           $mail->FromName = "PMO VI";
           // Clear recipients
           $mail->ClearAllRecipients();
           $mail->ClearReplyTos();
           $mail->Subject = "Reporte de seguimiento ID presupuestal " . $id_prepedido . " - " . $linea_pto_name . ": " . $linea_pto_desc; 
           $cuerpo = "<font size='2' face='verdana'><p>PMO le informa que el ID presupuestal <u>" . $id_prepedido . "</u> asociado al " . $linea_pto_name . " (" . $linea_pto_desc . "), cambi&oacute; del estado: <strong>" . $estado_anterior  . "</strong> al estado <strong>" . $estado_nuevo . "</strong>.  La modificaci&oacute;n fue realizada por " . $usuario->full_name  . "</p><p>Si desea conocer los detalles de este registro d&eacute; clic <a href=http://gpgp.etb.com.co/PMO/index.php?action=DetailView&module=l_prepedidos&record=" . $bean->id . ">ac&aacute;</a>.</p><p>Cordialmente,</p><p><a href=http://gpgp.etb.com.co/PMO>PMO Vicepresidencia Infraestructura</a></p></font>";
           // $mail->AddAddress('jaime.orjuelav@etb.com.co');
           // Finding email of the last dude that assigned the record
			$query = "SELECT getUserMail(before_value_string) AS email_address, getUserMail(after_value_string) AS email_address1 FROM l_prepedidos_audit WHERE field_name = 'assigned_user_id' AND date_created = (SELECT max(date_created) FROM   l_prepedidos_audit  WHERE field_name = 'assigned_user_id' AND parent_id = '" . $id . "') AND  parent_id = '" . $id . "'"; 
            $result = $bean->db->query($query,true);
            while ($row = $bean->db->fetchByAssoc($result)) {
              $mail->AddAddress($row['email_address']);
			  $mail->AddAddress($row['email_address1']);
            }
		   $mail->AddAddress('despapp1@etb.com.co');
		   $mail->AddAddress('jaimorjv@etb.com.co');
		   $mail->MsgHTML($cuerpo);
           $mail->isHTML(true);
           $mail->prepForOutbound();
           $mail->setMailerForSystem();
           //Send mail, log if there is error
           if (!$mail->Send()) {
              $GLOBALS['log']->fatal("ERROR: El correo NO PUDO SER ENVIADO!!!");
           }

        }

    }

}
?>

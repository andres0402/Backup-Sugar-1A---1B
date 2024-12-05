function parseLocalNum(str) {
	//Función para cambiar los puntos por nada y las comas por punto, para que posteriormente se pueda realizar el parseInt()
	// Tener en cuenta que el primer replace usa la misma estructura de la instrucción sed de shell.
	var localNum = parseFloat(str.replace(/\./g, "").replace(",", "."));
	if (isNaN(localNum)) {
		return 0;
	} else {
		return localNum;
	}
	
}

function check_form(formname) {
    bValid = false;
    if(typeof(siw)!='undefined'&&siw&&typeof(siw.selectingSomething)!='undefined'&&siw.selectingSomething) return false;
    bValid = validate_form(formname,'');
    if(!bValid) return false;
    var estado = document.getElementById("estado").value;
    var pre_estado = document.getElementById("pre_estado_c").value;	
    var solicitado_para = document.getElementById("solicitado_para_c").value;	
    var disponible_linea_pto = parseLocalNum(document.getElementById("disponible_linea_pto_c").value);
	var id_linea_pto = document.getElementById("l_lineas_presupuesto_l_prepedidosl_lineas_presupuesto_ida").value;
    var planeado_linea_pto_mes = parseLocalNum(document.getElementById("valor_maximo_mes_c").value);
    var valor_total_id = parseLocalNum(document.getElementById("valor_prepedido_cop").value);
	var valor_total_id_string = document.getElementById("valor_prepedido_cop").value;
    var valor_pagado_actas = parseLocalNum(document.getElementById("valor_acta_cop_c").value);
    var valor_cesta = parseLocalNum(document.getElementById("valor_cesta_cop_c").value);
    //var mes_causacion = parseLocalNum(document.getElementById("mes_causacion_c").value);
    var soporte = document.getElementById("assigned_user_name").value;
	var fecha_inicio = new Date(document.getElementById("fecha_inicio").value);


	var amiadmin = $.ajax({
		type: 'POST',
		url: "index.php?entryPoint=amiadmin",
		dataType : 'html',
		context: document.body,
		global: false,
		async:false,
		success:function(data){
			 return data;
		}
	}).responseText;	
	//var id_prepedido = document.getElementById("DetailView").elements["record"].value;		
    //alert(estado);

	//Se obtiene el saldo disponible de la línea de presupuesto
	if (id_linea_pto.length > 0){
		$.ajax({
			type: 'POST',
			url: "index.php?entryPoint=obtenerDisponibleLineaPto",
			data: {id_linea_pto: id_linea_pto},
			async: false,
			success:function(response){
				document.getElementById("disponible_linea_pto_c").value = parseFloat(parseFloat(response).toFixed(2));
				disponible_linea_pto = parseLocalNum(document.getElementById("disponible_linea_pto_c").value);
		 },
		 error: function(xhr, status, error) {
			console.error("Error en la llamada AJAX:", error);
			console.error("Status:", status);
			console.error("Response:", xhr.responseText);
		}
	});
	}

	if (valor_total_id > disponible_linea_pto) {
		alert('El valor solicitado supera el valor disponible en la línea de presupuesto, por lo que no es posible procesar su solicitud. Por favor corrija ese inconveniente para poder continuar.');
		return false;
	}

	var totalBolsas = 0;

	//Se obtiene el saldo disponible de las bolsas del contrato asociado
	$.ajax({
		type: 'POST',
		url: "index.php?entryPoint=obtenerTotalBolsasContrato",
		data: {id_linea_pto: id_linea_pto},
		async: false,
		success:function(response){
			totalBolsas = parseFloat(parseFloat(response).toFixed(2));
	 },
	 error: function(xhr, status, error) {
		console.error("Error en la llamada AJAX:", error);
		console.error("Status:", status);
		console.error("Response:", xhr.responseText);
	}
});

	if (valor_total_id > totalBolsas && totalBolsas != -1){
		alert('El valor solicitado supera el valor disponible en el saldo de las bolsas del contrato asociado, por lo que no es posible procesar su solicitud. Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} 

	var fecha_fin_contrato = "";

	//Se obtiene la fecha de finalización del contrato asociado
	$.ajax({
		type: 'POST',
		url: "index.php?entryPoint=obtenerFechaContrato",
		data: {id_linea_pto: id_linea_pto},
		async: false,
		success:function(response){
			fecha_fin_contrato = response;
	 },
	 error: function(xhr, status, error) {
		console.error("Error en la llamada AJAX:", error);
		console.error("Status:", status);
		console.error("Response:", xhr.responseText);
	}
});

	if (fecha_fin_contrato.length > 0){
		fecha_fin_contrato = new Date(fecha_fin_contrato);
		if (fecha_fin_contrato < fecha_inicio){
			alert('La fecha de finalización del contrato asociado es anterior a la fecha de causación del ID presupuestal, por lo que no es posible procesar su solicitud. Por favor corrija ese inconveniente para poder continuar.');
			return false;
		}
	}
	


	if (valor_pagado_actas == 0 && estado == "Acta_ID_total") {
           alert('No está permitido pasar al estado de Acta ID total, si NO ha registrado ninguna Hoja de Entrada. Si lo que necesita es liberar el valor del ID, por que no lo va a utilizar entonces anule el pedido que lo generó');
           return false;		
	} else if (valor_cesta == 0 && estado == "cesta_proceso_nuevo") {
           alert('No está permitido pasar al estado de Cesta creada para adición o proceso nuevo, si NO ha registrado ninguna Cesta. Si lo que necesita es liberar el valor del ID, una alternativa procedimentalmente más adecuada sería anular el ID. Consulte la documentación de ayuda al respecto.');
           return false;		
	} else if (valor_cesta == 0 && estado == "cesta_liberada") {
           alert('No está permitido pasar al estado de Cesta liberada por el facultado, si NO ha registrado ninguna Cesta. Si lo que necesita es liberar el valor del ID, una alternativa procedimentalmente más adecuada sería anular el ID. Consulte la documentación de ayuda al respecto.');
           return false;		
	} else if (valor_total_id < 0 && solicitado_para != "Contratacion"  && amiadmin == 0) { 
           alert('El valor del ID en ningún caso puede ser negativo!!!. Por favor corrija ese inconveniente para poder continuar.');
           return false;
    } else if (estado == "cesta_proceso_nuevo" && pre_estado != "cesta_solicitada") {   
        alert('Sólo está permitido pasar al estado Cesta creada para adición o proceso nuevo desde el estado cesta solicitada para adición o proceso nuevo.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "cesta_solicitada" && (pre_estado != "No_iniciado" || solicitado_para != "Contratacion")) {   
        alert('Sólo está permitido solicitar una cesta para adición o proceso nuevo desde el estado No iniciado y que el ID haya sido solicitado para un Proceso de Contratación.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "Anulado" && pre_estado != "No_iniciado" && solicitado_para != "Contratacion") {   
        alert('Sólo está permitido Anular IDs que hayan solicitados para procesos de contratación y que se encuentren en estado No iniciado.  Si lo que desea es anular un ID solicitado para pedido, DEBE anular el pedido y en consecuencia se anulará todos los IDs asociados a éste.');
		return false;
	} else if (estado == "Negado") {   
        alert('El estado de Negado sólo puede ser establecido por la herramienta cuando realiza los cálculos para la creación de IDs presupuestales.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "Prepedido_cargado_SAP" && pre_estado != "Prepedido_cargado_SAP" && pre_estado != "No_iniciado") {   
        alert('Sólo está permitido pasar al estado de Registro de prepedido o provisión desde el estado de No Inciado.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "Prepedido_cargado_SAP" && solicitado_para != "Pedido") {   
        alert('Sólo está permitido pasar al estado de Registro de prepedido o provisión si el IDs fue solicitado para Pedido.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "Acta_ID_toma_parcial" && pre_estado != "Prepedido_cargado_SAP" && pre_estado != "No_iniciado" && pre_estado != "Acta_ID_total" && pre_estado != "Acta_ID_toma_parcial") {   
        alert('Sólo está permitido pasar al estado Registro de hojas de entrada desde el estado Registro de Prepedido o Provisión o desde el estado No iniciado y que el ID haya sido solicitado para Pedido.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "Acta_ID_toma_parcial" && solicitado_para != "Pedido") {   
        alert('Sólo está permitido pasar al estado Registro de hojas de entrada desde el estado Registro de Prepedido o Provisión o desde el estado No iniciado y que el ID haya sido solicitado para Pedido.  Por favor corrija ese inconveniente para poder continuar.');
		return false;		
	} else if (estado == "Acta_ID_total" && pre_estado != "Acta_ID_toma_parcial" && pre_estado != "Acta_ID_total" && pre_estado != "Prepedido_cargado_SAP" && pre_estado != "No_iniciado") {
        alert('No está permitido pasar al estado Acta ID total desde el estado en que está en este momento o el ID no fue solicitado para Pedido.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "Acta_ID_total" && solicitado_para != "Pedido") {
        alert('No está permitido pasar al estado Acta ID total desde el estado en que está en este momento o el ID no fue solicitado para Pedido.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "No_iniciado" && pre_estado != "No_iniciado") {   
        alert('No está permitido pasar al estado No iniciado desde el estado en que está en este momento.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (soporte == "") {   
        alert('Es OBLIGATORIO asociar un Soporte Administrativo que es quien gestiona el ID presupuestal.  Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (valor_total_id > disponible_linea_pto) {
		alert('El valor solicitado supera el valor disponible en la línea de presupuesto por lo que no es posible procesar su solicitud. Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (valor_total_id_string.length === 0) {
		alert('DEBE colocar un valor solicitado para el ID ya sea en COP$ o en USD$. Por favor corrija ese inconveniente para poder continuar.');
		return false;
	//} else if (valor_prepedido_usd > 0 &&  trm_gp < 1500) {
	//	alert('Si especificó un valor solicitado en USD$ entonces DEBE colocar la TRM con la que realizó la verficación presupuestal. Por favor corrija ese inconveniente para poder continuar.');
	//	return false;
	//} else if (valor_acta_usd > 0 &&  trm_acta < 1500) {
	//	alert('Si especificó un valor en USD$ en el acta entonces DEBE colocar la TRM con la que se calculó el pago.  Si no tiene el valor exacto, haga una estimación sensata de ese valor. Por favor corrija ese inconveniente para poder continuar.');
	//	return false;
	//} else if (estado == "Prepedido_cargado_SAP" && valor_prepedido_usd > 0 && trm_prepedido < 1500) {
	//	alert('Si gestionó un prepedido en SAP para un valor en USD$ entonces DEBE colocar la TRM con la que se calculó el prepedido.  Si no tiene el valor exacto, haga una estimación sensata de ese valor. Por favor corrija ese inconveniente para poder continuar.');
	//	return false;
	//} else if ((estado == "Acta_ID_total" || estado == "Acta_ID_toma_parcial") && valor_prepedido_usd > 0 && valor_acta_usd == 0) {
	//	alert('Si gestionó un ID en USD$, éste DEBERÍA causar en USD$. El valor del acta en consecuencia DEBE ser coherente con el de la solicitud. Por favor corrija ese inconveniente para poder continuar.');
	//	return false;
	//} else if ((estado == "Acta_ID_total" || estado == "Acta_ID_toma_parcial") && valor_prepedido_cop > 0 && valor_acta_cop == 0) {
	//	alert('Si gestionó un ID en COP$, éste DEBERÍA causar en COP$. El valor del acta en consecuencia DEBE ser coherente con el de la solicitud. Por favor corrija ese inconveniente para poder continuar.');
	//	return false;
	} 
	// else if (mes_causacion == 0 ) {
	// 	alert('Debe especificar el mes en el que se planea generar el pago del rubro solicitado en este ID');
	// 	return false;
	// //} else if ((estado == "Acta_ID_total" || estado == "Acta_ID_toma_parcial") && numero_acta == "") {
	// //	alert('Falta diligenciar el campo de Número o Números de acta generadas con este ID.');
	// //	return false;
	// //} else if ((estado == "Acta_ID_total" || estado == "Acta_ID_toma_parcial") && consecutivo_sap == "") {
	// //	alert('Falta diligenciar el campo de Consecutivo SAP generado con el prepedido y/o Pedido.');
	// //	return false;
	// } 
	else if (valor_total_id > planeado_linea_pto_mes) {
		var confirma = confirm('El valor solicitado supera el valor planeado para el mes correspondiente.  Está seguro de continuar teniendo presente que podría presentarse sobrecausación? (Si elige ACEPTAR, se procesará la solicitud y se enviará correo de notificación a PMO.  Si elige CANCELAR, podrá editar la solicitud).');
		if (confirma) {
			if (valor_total_id > disponible_linea_pto) {
				alert('El valor solicitado supera el valor disponible en la línea de presupuesto por lo que no es posible procesar su solicitud. Por favor corrija ese inconveniente para poder continuar.');
				return false;
			} else if (estado == "Acta_ID_total") {
				 var confirma = confirm('Ha seleccionado el estado: Acta ID total, así que se cerrará este ID y se creará una nota crédito con la diferencia a favor (si la hubiere).  Está seguro que desea realizar esta acción?');
				 if (confirma) {
				    return true;
				 } else {
				    return false;
				 }
			} else {
					return true;
		}
	  }		
	} else if (valor_total_id > disponible_linea_pto) {
		alert('El valor solicitado supera el valor disponible en la línea de presupuesto por lo que no es posible procesar su solicitud. Por favor corrija ese inconveniente para poder continuar.');
		return false;
	} else if (estado == "Acta_ID_total") {
		var confirma = confirm('Ha seleccionado el estado: Acta ID total, así que se cerrará este ID y se creará una nota crédito con la diferencia a favor (si la hubiere).  Está seguro que desea realizar esta acción?');
		if (confirma) {
			return true;
		} else {
			return false;
		} 
	} else {
	  return true;
	} 
}


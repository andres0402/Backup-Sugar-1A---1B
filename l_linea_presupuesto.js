function parseLocalNum(str) {
	//Función para cambiar los puntos por nada y las comas por punto, para que posteriormente se pueda realizar el parseInt()
	// Tener en cuenta que el primer replace usa la misma estructura de la instrucción sed de shell.
	return parseFloat(str.replace(/\./g, "").replace(",", "."));
}

function check_form(formname) {
	bValid = false;
	if(typeof(siw)!='undefined'&&siw&&typeof(siw.selectingSomething)!='undefined'&&siw.selectingSomething) return false;
	bValid = validate_form(formname,'');
	if(!bValid) return false;
	var asignado = document.getElementById("assigned_user_name").value;
	
	if (asignado != "Vicepresidencia de Tecnología" && asignado != "Vicepresidencia Empresas CI") { 
           alert('Los únicos valores permitidos para la asignación de una línea de presupuesto son: Vicepresidenci de Tecnología o Vicepresidencia Empresas CI. Por favor modifique la asignación de la línea de presupuesto a alguno de estos dos usuarios.');
           return false;
	}

	var id_bolsa = document.getElementById('b_bolsas_l_lineas_presupuesto_1b_bolsas_ida').value;
	var id_contrato = document.getElementById('c_contratos_l_lineas_presupuestoc_contratos_ida').value;

	id_contrato_bolsa = "";

	if (id_bolsa.length > 0){
		$.ajax({
			type: 'POST',
			url: "index.php?entryPoint=validarContratoBolsa",
			data: {id_bolsa: id_bolsa},
			async: false,
			success:function(response){
				id_contrato_bolsa = response;
		 },
		 error: function(xhr, status, error) {
			console.error("Error en la llamada AJAX:", error);
			console.error("Status:", status);
			console.error("Response:", xhr.responseText);
		}
	});
	}

if (id_contrato_bolsa.length > 0){
	if (id_contrato.length === 0){
		id_contrato = id_contrato_bolsa;
		document.getElementById('c_contratos_l_lineas_presupuestoc_contratos_ida').value = id_contrato_bolsa;
	}
	if (!id_contrato_bolsa.includes(id_contrato)){
		alert("La línea presupuestal debe tener asociado el mismo contrato que la bolsa. Por favor, corrija este inconveniente para continuar");
		return false;
	}
}

return true;
}


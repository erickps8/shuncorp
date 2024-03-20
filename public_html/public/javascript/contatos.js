function validarCampos(){
	
	if(document.gravaontatos.nome.value==""){
		//alert('O Nome não pode ficar em branco!');
		alertaPadrao('Erro!', 'O Nome não pode ficar em branco!', 'erro', 110, 300);
		document.gravaontatos.nome.focus();
		return false; 	
	}else{
		return true;
	}
	return false;
}

function validarCamposempresa(){
	
	if(document.gravaempresa.empresa.value==""){
		//alert('O Nome não pode ficar em branco!');
		alertaPadrao('Erro!', 'O Nome da empresa não pode ficar em branco!', 'erro', 110, 300);
		document.gravaempresa.empresa.focus();
		return false; 	
	}else{
		return true;
	}
	return false;
}

function validarCamposfilial(){
	
	if(document.gravaempresa.empresa.value==""){
		//alert('O Nome não pode ficar em branco!');
		alertaPadrao('Erro!', 'O Nome da empresa não pode ficar em branco!', 'erro', 110, 300);
		document.gravaempresa.empresa.focus();
		return false; 	
	}else{
		return true;
	}
	return false;
}

function getEndereco() {
	// Se o campo CEP não estiver vazio
	if($.trim($("#cep").val()) != ""){
		document.getElementById('carregar').style.display = "block";
		/* 
			Para conectar no serviço e executar o json, precisamos usar a função
			getScript do jQuery, o getScript e o dataType:"jsonp" conseguem fazer o cross-domain, os outros
			dataTypes não possibilitam esta interação entre domínios diferentes
			Estou chamando a url do serviço passando o parâmetro "formato=javascript" e o CEP digitado no formulário
			http://cep.republicavirtual.com.br/web_cep.php?formato=javascript&cep="+$("#cep").val()
		*/
		$.getScript("http://cep.republicavirtual.com.br/web_cep.php?formato=javascript&cep="+$("#cep").val(), function(){
			// o getScript dá um eval no script, então é só ler!
			//Se o resultado for igual a 1
	  		if(resultadoCEP["resultado"]){
				// troca o valor dos elementos
				$("#rua").val(unescape(resultadoCEP["tipo_logradouro"])+": "+unescape(resultadoCEP["logradouro"]));
				$("#bairro").val(unescape(resultadoCEP["bairro"]));
				$("#cidade").val(retira_acentos(unescape(resultadoCEP["cidade"])));
				$("#uf").val(unescape(resultadoCEP["uf"]));
				$("#cidade").val(retira_acentos(unescape(resultadoCEP["cidade"])));
				document.getElementById('carregar').style.display = "none";	
			}else{
				alert("Endereço não encontrado");
				document.getElementById('carregar').style.display = "none";	
			}
		});	
				
	}
	
				
}

function retira_acentos(palavra) {
	com_acento = 'áàãâäéèêëíìîïóòõôöúùûüçÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÖÔÚÙÛÜÇ';
	sem_acento = 'aaaaaeeeeiiiiooooouuuucAAAAAEEEEIIIIOOOOOUUUUC';
	nova='';
	for(i=0;i<palavra.length;i++) {
		if (com_acento.search(palavra.substr(i,1))>=0) {
			nova+=sem_acento.substr(com_acento.search(palavra.substr(i,1)),1);
		}
		else {
			nova+=palavra.substr(i,1);
		}
	}
	return nova;
}

function apagaEndereco(){
	document.gravaontatos.cep.value = "";
	document.gravaontatos.rua.value = "";
	document.gravaontatos.bairro.value = "";
	document.gravaontatos.pais.value = 1;
	document.gravaontatos.uf.value = 1;
	document.gravaontatos.cidade.value = 0;
	
	if(document.gravaontatos.endempresa.checked == true){
		document.gravaontatos.cep.disabled = true;
		document.gravaontatos.rua.disabled = true;
		document.gravaontatos.bairro.disabled = true;
		document.gravaontatos.pais.disabled = true;
		document.gravaontatos.uf.disabled = true;
		document.gravaontatos.cidade.disabled = true;
	}else{
		document.gravaontatos.cep.disabled = false;
		document.gravaontatos.rua.disabled = false;
		document.gravaontatos.bairro.disabled = false;
		document.gravaontatos.pais.disabled = false;
		document.gravaontatos.uf.disabled = false;
		document.gravaontatos.cidade.disabled = false;
	}
}

var alertaPadrao = function(titulo, msg, tipo, altura, largura) {
	$('body').append('<a href="#" id="alerta-padrao"></a>');
	$('#alerta-padrao').m2brDialog({
			draggable: true,
			texto: msg,
			tipo: tipo,
			titulo: titulo,
			altura: altura,
			largura: largura,
			botoes: {
				1: {
					label: 'Fechar',
					tipo: 'fechar'
				}
			}									   
	});
	$('#alerta-padrao')
		.click()
		.remove();
};
$(function(){
	
	
});

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

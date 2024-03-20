var xmlHttp;

function buscaCnpj(cnpj){
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscacnpj/cnpj/"+cnpj;
	xmlHttp.onreadystatechange=resbuscaCnpj;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function resbuscaCnpj(){	
	if (xmlHttp.readyState==4){
		var texto = xmlHttp.responseText;
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		
		if(texto == 1){
			document.getElementById('rescnpj').innerHTML='CPF/CNPJ já cadastrado!';
			document.getElementById('cpf_cnpj').value = '';
		}else{
			document.getElementById('rescnpj').innerHTML='';			
		}
	}
}

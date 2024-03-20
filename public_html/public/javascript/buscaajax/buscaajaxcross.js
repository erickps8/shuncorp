var xmlHttp;
var tipo;

function buscaCodigoporfornecedor(codigo,forn,tp){
	tipo = tp;
	
	if (forn.length==0){
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscacrossporfornecedor/idprod/"+codigo+"/forn/"+forn;
	xmlHttp.onreadystatechange=stateChangecross;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function stateChangecross(){	
	if (xmlHttp.readyState==4){
		var texto = xmlHttp.responseText;
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		
		if(tipo == 2){
			document.getElementById('codigofornecedortai').innerHTML = texto;
		}else{
			document.getElementById('codigofornecedor').innerHTML = texto;
		}
	}
}

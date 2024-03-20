var xmlHttp;

var ufcod;
function buscaQtcontatos(uf){
	ufcod = uf;
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscaqtcontatos/uf/"+uf;
	xmlHttp.onreadystatechange=stateChangeqtcontatos;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}


function stateChangeqtcontatos(){	
	if (xmlHttp.readyState==4){
		document.getElementById(ufcod).innerHTML=xmlHttp.responseText;
	}
}




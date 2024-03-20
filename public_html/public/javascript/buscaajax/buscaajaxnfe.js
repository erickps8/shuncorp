var xmlHttp;

function buscaNfe(forn,idpag){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="/admin/administracao/buscafinnfe/forn/"+forn+"/pag/"+idpag;
	xmlHttp.onreadystatechange=stateChangedbuscanfe;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedbuscanfe(){
	if (xmlHttp.readyState==4){
		jModal(xmlHttp.responseText, "Fretes", 600, 400);
	}
}

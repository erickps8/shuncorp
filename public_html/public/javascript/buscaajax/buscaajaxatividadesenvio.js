var xmlHttp

function Mostrasolicitanteativ(user){
	
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	//var url="http://www.ztlbrasil.com.br/admin/administracao/buscaatividades";
	var url="/admin/administracao/buscaatividades";
	url=url+"/id/"+user;
	url=url+"/sid="+Math.random();
	xmlHttp.onreadystatechange=stateChangedsol;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedsol(){
	if (xmlHttp.readyState==4){
		//document.novogar.id_or.value,
		document.getElementById('divsol').innerHTML=xmlHttp.responseText;
	}
}

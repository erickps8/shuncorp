var xmlHttp;
var span = 0;

function enviaMail(){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="/admin/marketing/enviarmensagem";
	xmlHttp.onreadystatechange=estadoResposta;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoResposta(){
	if (xmlHttp.readyState==4){
		document.getElementById('recHint').innerHTML=xmlHttp.responseText;
	}
}
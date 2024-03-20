var xmlHttp
var span = 0;

function MostraOpcaomediaestoque(){
	
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	//var url="http://www.ztlbrasil.com.br/admin/painel/buscamediaestoque/sid="+Math.random();
	var url="/admin/painel/buscamediaestoque/sid="+Math.random();
	//url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChangedest;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedest(){
	if (xmlHttp.readyState==4){
		//document.novogar.id_or.value,
		document.getElementById('recHint').innerHTML=xmlHttp.responseText;
	}
	
	
}
var xmlHttp

function MostraParceiros(tipo){
	
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscatipoparceiro/tipo/"+tipo;
	url=url+"/sid="+Math.random();
	xmlHttp.onreadystatechange=stateChangedtipoparceiro;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function stateChangedtipoparceiro(){
	if (xmlHttp.readyState==4){
		document.getElementById('divparc').innerHTML=xmlHttp.responseText;
	}
}

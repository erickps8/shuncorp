var xmlHttp
var tip = 0;

function Mostrasubpcontas(id,tipo){
	tip = tipo;
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	if(id!="all"){
		var url="/admin/administracao/buscasubplanocontas/cat/"+id+"/tp/"+tip;
		xmlHttp.onreadystatechange=stateChangedsubgrupos;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}else{
		document.getElementById('subplano').innerHTML="";
	}
}

function stateChangedsubgrupos(){
	if (xmlHttp.readyState==4){
		document.getElementById('subplano_'+tip).innerHTML=xmlHttp.responseText;
	}
}

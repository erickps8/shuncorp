var xmlHttp

function Mostrasubgrupos(id,tipo){
	
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	if(id!="all"){
		//var url="http://www.ztlbrasil.com.br/admin/cadastro/buscasubgrupo/id_grupo/"+id+"/tipo/"+tipo;
		var url="/admin/cadastro/buscasubgrupo/id_grupo/"+id+"/tipo/"+tipo;
		url=url+"/sid="+Math.random();
		xmlHttp.onreadystatechange=stateChangedsubgrupos;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}else{
		document.getElementById('subgrupo').innerHTML="";
	}
}

function stateChangedsubgrupos(){
	if (xmlHttp.readyState==4){
		document.getElementById('subgrupo').innerHTML=xmlHttp.responseText;
	}
}

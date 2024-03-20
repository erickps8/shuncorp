var xmlHttp
var span = 0;

function Mostravendas(id,dt){
	
	if (id.length==0){
		document.getElementById(str).innerHTML="";
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/venda/buscamediavendas/idprod/"+id+"/dt/"+dt;
	url=url+"/sid/"+Math.random();
	xmlHttp.onreadystatechange=stateChangedmediavendas;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	span = id;
}

function stateChangedmediavendas(){
	if (xmlHttp.readyState==4){
		document.getElementById('m_'+span).innerHTML=xmlHttp.responseText;
	}
}

function Mostracompra(id){
	
	if (id.length==0){
		document.getElementById(str).innerHTML="";
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/venda/buscamediacompras/idprod/"+id;
	url=url+"/sid/"+Math.random();
	xmlHttp.onreadystatechange=stateChangedcompra;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	span = id;
}

function stateChangedcompra(){
	if (xmlHttp.readyState==4){
		document.getElementById('c_'+span).innerHTML=xmlHttp.responseText;
	}
}


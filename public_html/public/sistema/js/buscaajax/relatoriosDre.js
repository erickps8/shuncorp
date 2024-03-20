var xmlHttp;
var idpl;

function buscaDre(id,tipo){
	
	idpl = id;
	
	if (idpl.length==0){
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/relatorios/buscadre/idpl/"+idpl+"/tp/"+tipo;
	xmlHttp.onreadystatechange=retornoDre;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function retornoDre(){
	if (xmlHttp.readyState==4){
		document.getElementById(idpl).innerHTML=xmlHttp.responseText;
	}
}


function buscaDretotal(id){
	
	idpl = id;
	
	if (idpl.length==0){
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/relatorios/buscadre/idpl/"+idpl+"/buscatotal/1";
	xmlHttp.onreadystatechange=retornoDretotal;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function retornoDretotal(){
	if (xmlHttp.readyState==4){
		document.getElementById('0').innerHTML=xmlHttp.responseText;
	}
}

function buscaDrecontas(id){
	
	idpl = id;
	
	if (idpl.length==0){
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/relatorios/buscadre/idpl/"+idpl+"/buscacontas/1";
	xmlHttp.onreadystatechange=retornoDrecontas;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function retornoDrecontas(){
	if (xmlHttp.readyState==4){
		document.getElementById(idpl).innerHTML=xmlHttp.responseText;
	}
}

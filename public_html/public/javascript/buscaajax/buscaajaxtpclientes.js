var xmlHttp;
var span = 0;
var tipo;

function buscaClientesportipo(tipo){
	var urlbusca;
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	
	if(tipo == 'forcavenda'){
		urlbusca = "/forcavenda/1";
	}else if(tipo == 'func'){
		urlbusca = "/func/1";
	}
	
	var url="/admin/administracao/buscaclientesportipo"+urlbusca;
	xmlHttp.onreadystatechange=stateChangedest;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedest(){
	if (xmlHttp.readyState==4){
		document.getElementById('divforcavenda').innerHTML=xmlHttp.responseText;
	}
	
	
}
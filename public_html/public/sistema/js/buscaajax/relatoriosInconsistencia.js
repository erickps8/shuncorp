var xmlHttp;
var tipo;

function buscainconsistencias(tp){
	
	tipo = tp;
	
	xmlHttp=GetXmlHttpObject();
	
	var url="/admin/relatorios/buscainconsistencias/tp/"+tipo;
	xmlHttp.onreadystatechange=retornoInconsistencias;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function retornoInconsistencias(){
	if (xmlHttp.readyState==4){
		if(tipo == 1){
			$("#financeiro").html(xmlHttp.responseText);
			buscainconsistencias();
		}else{
			$("#estoque").html(xmlHttp.responseText);
		}
	}
}


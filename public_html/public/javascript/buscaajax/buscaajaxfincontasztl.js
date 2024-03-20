var xmlHttp;

function Buscacontasconcilia(tipo,id,conta,tpant,bancobusca){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="/admin/administracao/buscacontasfinztl/tipo/"+tipo+"/idc/"+id+"/conta/"+conta+"/tpant/"+tpant+"/bancobusca/"+bancobusca;
	xmlHttp.onreadystatechange=stateChangedconcilia;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedconcilia(){
	if (xmlHttp.readyState==4){
		document.getElementById('content').innerHTML=xmlHttp.responseText;
	}
}

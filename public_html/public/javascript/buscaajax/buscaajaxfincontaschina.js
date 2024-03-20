var xmlHttp

function Buscacontasconcilia(tipo,id,conta,tpant,bancobusca){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="/admin/administracao/buscacontasfin/tipo/"+tipo+"/idc/"+id+"/conta/"+conta+"/tpant/"+tpant+"/bancobusca/"+bancobusca;
	xmlHttp.onreadystatechange=stateChangedconcilia;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function Buscapedidoscompra(id, tipo, fornecedor, npurch){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="/admin/administracao/buscacontasfinped/conta/"+id+"/tipo/"+tipo+"/forn/"+fornecedor+"/npurc/"+npurch;
	xmlHttp.onreadystatechange=stateChangedconcilia;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);		
}

function Buscainvoice(id, fornecedor){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	var url="/admin/administracao/buscainvoice/conta/"+id+"/buscacli/"+fornecedor+"/financeiro/1";
	xmlHttp.onreadystatechange=stateChangedconcilia;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
		
}

function stateChangedconcilia(){
	if (xmlHttp.readyState==4){
		document.getElementById('content').innerHTML=xmlHttp.responseText;
	}
}

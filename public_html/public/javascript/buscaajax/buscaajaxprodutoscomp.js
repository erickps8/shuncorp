var xmlHttp

function MostraOpcaobprodcomp(idc){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	//var url="http://www.ztlbrasil.com.br/admin/cadastro/buscaproduto/idcli/"+idc+"/sid="+Math.random();
	var url="/admin/cadastro/buscaproduto/idcli/"+idc+"/sid="+Math.random();
	xmlHttp.onreadystatechange=stateChangebprodcomp;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangebprodcomp(){
	if (xmlHttp.readyState==4){
		document.getElementById('divreccomp').innerHTML=xmlHttp.responseText;
	}	
}

function buscaDespesasfiscais(uf){
	
	xmlHttp=GetXmlHttpObject();
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscadespesasfiscais/uf/"+uf;
	xmlHttp.onreadystatechange=stateChangedesp;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedesp(){
	if (xmlHttp.readyState==4){
		document.getElementById('despesasfiscais').innerHTML=xmlHttp.responseText;
	}	
}
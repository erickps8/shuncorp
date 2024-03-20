var xmlHttp;

/*-----Busca cidades ibge por ID do estado----------------------------------------
 Usado em:
 parceirosAction();
 */
function buscaCidadesibge(idestado,tp){
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscacidadeibge/tipo/"+tp+"/idestado/"+idestado+"/sid/"+Math.random();
	if(tp==1){
		xmlHttp.onreadystatechange=stateChangedcidibge;
	}else{
		xmlHttp.onreadystatechange=stateChangedcidibge2;
	}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function stateChangedcidibge(){	
	if (xmlHttp.readyState==4){
		document.getElementById('divcidade').innerHTML=xmlHttp.responseText;
	}
}

function stateChangedcidibge2(){	
	if (xmlHttp.readyState==4){
		document.getElementById('divcidade2').innerHTML=xmlHttp.responseText;
	}
}


/*-----Busca Estados por ID do pais----------------------------------------
Usado em:
parceirosAction();
*/
function Mostramontauf(pais,tp){
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscauf/t/"+tp+"/pais/"+pais+"/sid/"+Math.random();
	if(tp==1){
		xmlHttp.onreadystatechange=stateChangeduf;
	}else{
		xmlHttp.onreadystatechange=stateChangedufcob;
	}
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function stateChangeduf(){	
	if (xmlHttp.readyState==4){
		document.getElementById('divuf').innerHTML=xmlHttp.responseText;
	}
}

function stateChangedufcob(){	
	if (xmlHttp.readyState==4){
		document.getElementById('divcob').innerHTML=xmlHttp.responseText;
	}
}


function buscaCidades(estado){	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscacidade/uf/"+estado;
	xmlHttp.onreadystatechange=stateChangedcidade;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function stateChangedcidade(){	
	if (xmlHttp.readyState==4){
		document.getElementById('divcid').innerHTML=xmlHttp.responseText;
	}
}



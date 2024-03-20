var xmlHttp;
var contato;
//--- busca filiais ----------------------------------------------------------------
function buscaFiliais(emp){
	contato = emp;
	
	document.getElementById('M'+contato).style.display = 'table-row';
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscacontatosquar/tp/2/emp/"+contato;
	xmlHttp.onreadystatechange=resbuscaFiliais;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function resbuscaFiliais(){	
	if (xmlHttp.readyState==4){
		document.getElementById('M'+contato).innerHTML=xmlHttp.responseText;
	}
}

//--- busca contatos filiais ----------------------------------------------------------------
function buscaContatosfiliais(emp){
	contato = emp;
	
	document.getElementById('F'+contato).style.display = 'table-row';
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscacontatosquar/tp/1/empfilial/"+contato;
	xmlHttp.onreadystatechange=resbuscaContatosfiliais;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function resbuscaContatosfiliais(){	
	if (xmlHttp.readyState==4){
		document.getElementById('F'+contato).innerHTML=xmlHttp.responseText;
	}
}


//--- busca contatos matriz ----------------------------------------------------------------
function buscaContatosmatriz(emp){
	contato = emp;
	document.getElementById('M'+contato).style.display = 'table-row';
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/cadastro/buscacontatosquar/tp/0/empmatriz/"+contato;
	xmlHttp.onreadystatechange=resbuscaContatosmatriz;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function resbuscaContatosmatriz(){	
	if (xmlHttp.readyState==4){
		document.getElementById('M'+contato).innerHTML=xmlHttp.responseText;
	}
}


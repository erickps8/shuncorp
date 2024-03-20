var xmlHttp;


function mostarPainelpr(nTr){
    nTr = document.getElementById(nTr);
    if(nTr.style.display=="none"){
		nTr.style.display = "block";
	}else{
		   nTr.style.display = "none";
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
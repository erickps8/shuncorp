var xmlHttp
//-- Lista jogadores ----------------------
function Mostralistajogadores(jog){

	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
	alert ("Seu browser não suporta AJAX!");
	return;
	}
	
	var url="/admin/publico/buscainfo/jog/"+jog;
	
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

//-- Remove jogadores ----------------------
function removeJogadorlista(jog){

	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
	alert ("Seu browser não suporta AJAX!");
	return;
	}
	
	var url="/admin/publico/buscainfo/rem/1/jog/"+jog;
	
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

//-- Atualiza lista ----------------------
function atualizaLista(){
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
	alert ("Seu browser não suporta AJAX!");
	return;
	}
	
	var url="/admin/publico/buscajog";
	
	xmlHttp.onreadystatechange=stateChangedjog;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedjog(){
	if (xmlHttp.readyState==4){
		document.getElementById('jog').innerHTML=xmlHttp.responseText;
		atualizaPartidavit();
	}	
}

//-- Atualiza lista partida ----------------------
function atualizaPartidavit(){
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
	alert ("Seu browser não suporta AJAX!");
	return;
	}
	
	var url="/admin/publico/buscapartida";
	
	xmlHttp.onreadystatechange=stateChangedpartvit;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedpartvit(){
	if (xmlHttp.readyState==4){
		document.getElementById('part').innerHTML=xmlHttp.responseText;
	}	
}


//-- gambiarra ---------------------------------


function atualizaPartida(jog){
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
	alert ("Seu browser não suporta AJAX!");
	return;
	}
	
	var url="/admin/publico/buscapartida/jog/"+jog;
	
	xmlHttp.onreadystatechange=stateChangedpart;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChangedpart(){
	if (xmlHttp.readyState==4){
		document.getElementById('part').innerHTML=xmlHttp.responseText;
		reexibiJogadorfila();
	}	
}

//-- Gambiarra para listar jogadores ----------------------
function reexibiJogadorfila(){

	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
	alert ("Seu browser não suporta AJAX!");
	return;
	}
	
	var url="/admin/publico/buscainfo";
	
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChanged(){
	
	if (xmlHttp.readyState==4){
		document.getElementById('fila').innerHTML=xmlHttp.responseText;
		atualizaLista();
	}	
}	


function GetXmlHttpObject(){
	var xmlHttp=null;
	try{
	// Firefox, Opera 8.0+, Safari
	xmlHttp=new XMLHttpRequest();
	}catch (e){
		// Internet Explorer
		try
		{
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}
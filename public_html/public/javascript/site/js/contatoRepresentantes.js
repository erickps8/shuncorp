var xmlHttp;
function buscaRepresentantes(reg){
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/contato/buscarepresentantes/regiao/"+reg;
	xmlHttp.onreadystatechange=stateChangeregioes;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}


function stateChangeregioes(){	
	if (xmlHttp.readyState==4){
		document.getElementById('content').innerHTML=xmlHttp.responseText;
	}
}

function GetXmlHttpObject(){
	var xmlHttp=null;
	try	{
	// Firefox, Opera 8.0+, Safari
	xmlHttp=new XMLHttpRequest();
	}catch (e){
	// Internet Explorer
		try	{
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}catch (e)	{
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

var xmlHttp

function Mostrasubgrupos(id){
	
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/publico/buscasubgrupo/id_grupo/"+id;
	xmlHttp.onreadystatechange=stateChangedsubgrupos;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function stateChangedsubgrupos(){
	if (xmlHttp.readyState==4){
		document.getElementById('subgrupo').innerHTML=xmlHttp.responseText;
	}
}

function GetXmlHttpObject()
{
var xmlHttp=null;
try
{
// Firefox, Opera 8.0+, Safari
xmlHttp=new XMLHttpRequest();
}
catch (e)
{
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

var xmlHttp
var span = 0;

function MostraInforatividades(){
	
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	/*
	var url="/admin/administracao/buscainfo/";
	url=url+"sid="+Math.random();
	xmlHttp.onreadystatechange=stateChangedatividades;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null); */
}


function stateChangedatividades(){
	if (xmlHttp.readyState==4){
		if(xmlHttp.responseText!=""){
		document.getElementById('rodapePopup').style.display = 'block';
		document.getElementById('respPopup').innerHTML="<b>"+xmlHttp.responseText+"</b> cadastrou uma nova tarefa pra você!";
		}
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

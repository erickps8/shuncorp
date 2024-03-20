var xmlHttp

function Mostraidiomas(str){
	
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/administracao/buscaidioma";
	url=url+"/idioma/"+str;
	url=url+"/sid/"+Math.random();
	xmlHttp.onreadystatechange=stateChanged2;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
	//document.location.reload();
}

function stateChanged2()
{
if (xmlHttp.readyState==4)
{
//document.getElementById('divinfoq').innerHTML=xmlHttp.responseText;
}
}



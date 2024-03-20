var xmlHttp
var span = 0;

function MostraOpcao(str){
	
	  xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	document.getElementById('imgmanual').innerHTML='<img alt="Acesso ao Site da ZTL" src="/public/sistema/imagens/loaders/loader6.gif" />';
		  
	var url="/admin/manual/buscamanualgar";
	url=url+"/str/"+str;
	url=url+"/sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	span = str;
}

function stateChanged(){
	if (xmlHttp.readyState==4){
		document.getElementById('imgmanual').innerHTML=xmlHttp.responseText;
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


var xmlHttp
var span = 0;

function MostraOpcao(str){
	
	if (str.length==0){
		document.getElementById(str).innerHTML="";
		return;
	}
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	//var url="http://www.ztlbrasil.com.br/admin/venda/buscahistoricogarantia";
	var url="/admin/venda/buscahistoricogarantia";
	url=url+"?str="+str;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	span = str;
}

function stateChanged()
{
if (xmlHttp.readyState==4)
{
document.getElementById('m_'+span).innerHTML=xmlHttp.responseText;
}
}


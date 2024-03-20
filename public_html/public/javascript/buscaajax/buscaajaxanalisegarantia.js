var xmlHttp
var span = 0;

function MostraOpcao(op){
	
	if (op.length==0){
		document.getElementById(str).innerHTML="";
		return;
	}
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/venda/buscaanalisegarantia";
	url=url+"?buscatipo="+op;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function stateChanged()
{
if (xmlHttp.readyState==4)
{
//document.novogar.id_or.value,
document.getElementById('recHint').innerHTML=xmlHttp.responseText;
}
}


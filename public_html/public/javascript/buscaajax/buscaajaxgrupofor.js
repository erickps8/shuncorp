var xmlHttp

function Buscagruposfornecedor(idfor){
	
	xmlHttp=GetXmlHttpObject()
	
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	if(idfor!="0"){
		var url="/admin/compras/buscagruposfornecedor/idfor/"+idfor;
		url=url+"/sid="+Math.random();
		xmlHttp.onreadystatechange=stateChangedgruposfor;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
		document.getElementById('submit').style.display="block";
	}else{
		document.getElementById('submit').style.display="none";
	}
}

function stateChangedgruposfor(){
	if (xmlHttp.readyState==4){
		document.getElementById('buscgrupo').innerHTML=xmlHttp.responseText;
	}
}

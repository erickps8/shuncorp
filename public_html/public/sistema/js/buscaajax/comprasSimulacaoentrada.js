var xmlHttp;

function buscaProduto(){
	xmlHttp=GetXmlHttpObject();

	if ($("#codigo").val().length==0){
		$("#resultado").css({color:'#F00'}).html("");
		return;
	}
	
	var url="/admin/compras/buscaprodutos/codigo/"+$("#codigo").val();
	xmlHttp.onreadystatechange=resultadoBusca;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function resultadoBusca(){
	if (xmlHttp.readyState==4){
		var texto = xmlHttp.responseText;
		
		if(texto == 0){
			$("#resultado").css({color:'#F00'}).html("Produto não cadastrado!");
		}
	}
}

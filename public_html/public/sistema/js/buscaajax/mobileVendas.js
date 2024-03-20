var xmlHttp;

function buscaProduto(ped,str,qt){
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/mobile/vendas/buscaprodutopreco/ped/"+ped+"/codigo/"+str+"/qt/"+qt;
	xmlHttp.onreadystatechange=retornBuscaproduto;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function retornBuscaproduto(){
	if (xmlHttp.readyState==4){
		var texto = xmlHttp.responseText;
		
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		texto = texto.split("|");
		
		if(texto[0] == 1){
			window.location="/mobile/vendas/precos/ped/"+texto[1];
		}else if(texto[0] == 2){
			exibeModal('#cross');
			$('#desccross').html(texto[1]);
		}else if(texto[0] == 3){
			jAlert("Produto não encontrado","Erro!");			
		}else if(texto[0] == 4){
			jAlert("Este produto já está na lista!","Erro!");
		}
	}
	
}

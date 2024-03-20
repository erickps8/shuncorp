var xmlHttp;

function buscaProduto(str){
	if (str.length==0){
		document.getElementById("txtHint").innerHTML="";
		return;
	}
	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	var url="/admin/venda/buscaprodutovenda/q/"+str;
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	
}

function stateChanged(){
	if (xmlHttp.readyState==4){
		var texto = xmlHttp.responseText;
		texto = texto.replace(/\+/g," ");
		texto = unescape(texto);
		texto = texto.split("|");
		
		var arrId = document.getElementById('arrId').value;
		
		narrId = arrId.split(";");
		erro = 0;
		for(i=0;i < narrId.length; i++){
			if(texto[0]==narrId[i]){
				erro = 3;
			}
		}
		
		if(erro==3){
			document.getElementById('resultado').innerHTML = 'Este produto já está na lista!';
			document.getElementById('codigo').value = "";
			document.getElementById('codigo').focus();
		    return false;		    
		}else if(texto[0]=="erro1"){
			document.getElementById('resultado').innerHTML = 'Codigo incorreto!';
			document.getElementById('codigo').value = "";
			document.getElementById('codigo').focus();
		}else if(texto[0]=="erro4"){
			document.getElementById('resultado').innerHTML = 'Produto não disponível para venda!';
			document.getElementById('codigo').value = "";
			document.getElementById('codigo').focus();
		}else{
			document.getElementById('produto').value = texto[0];			
		}
	
	}
	
}
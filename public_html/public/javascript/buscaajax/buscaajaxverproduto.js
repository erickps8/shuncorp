var xmlHttp
var pesounit = 0;
var qtt = 0;

function MostraOpcao(str,v2,peso){
	
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	qtt = v2;
	pesounit = peso;
	 		
	var url="/admin/compras/buscaproduto";
	url=url+"/q/"+str+"/v2/"+v2;
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
				erro = 1;
			}
		}
		
		if(erro==1){
			document.getElementById('resultado').innerHTML = 'Este código já esta na lista';
		    return false;	    
		}else if(texto[0]=="erro1"){
			document.getElementById('resultado').innerHTML = 'Codigo incorreto';
			document.getElementById("codigo").focus();
		}else{
			window.location='/admin/compras/entradaestoqcad/prodped/'+texto[0]+'/qt/'+qtt+'/peso/'+pesounit;
		}	
	}
}

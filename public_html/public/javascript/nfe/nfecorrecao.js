
var xmlHttp;
var idnfe;
var idcce;

/*--------- grava dados da venda ------------------*/
function gravarDados(nfe){	
	idnfe = nfe;
	
	$('#divnfe').css({display:'block'});
	$("#resultadoprogresso").html("Salvando dados da CCe...");
	$("#etapaprogresso").html("1/4");	
	$('#barraprogresso').css({width: '10%'});
	
    $.post('/admin/nfe/gerarcartadecorrecao', {
    	idnfe: nfe,
    	correcao: $("#correcao").val()},    	
	    function(resposta) {
			texto = resposta.replace(/^\s+|\s+$/g,"");
			texto = unescape(texto);
			texto = texto.split(":");
			
			if(texto[0] == "idcce"){	
				idcce = texto[1];
				
				$('#barraprogresso').animate( {width: '20%', opacity: 1, left: '0px' }, function(){
					gerarCce();
	  	  		} );
			}else{
	        	tratarErros('Erro ao salvar dados da CCE...');
	        	$("#gerarNfe").attr("value","Tentar novamente");
	        }
	    });	
}


/*--------- gerar chave nfe ------------------*/
function gerarCce(){	
	
	$("#resultadoprogresso").html("Aguardadno resposata da Sefaz...");
			
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarcartadecorrecao/idcce/"+idcce+"/idnfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornocce;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornocce(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessoCcesefaz"){
			$("#etapaprogresso").html("2/4");
			$('#barraprogresso').animate( {width: '50%', opacity: 1, left: '0px' }, function(){
				gerarDacce();
  	  		} );
			
		}else{
			tratarErros('Erro ao gerar CCE ('+texto+')...');
			$("#gerarNfe").attr("value","Tentar novamente");
		}
	}	
}

/*--------- Gerar DACCE ------------------*/
function gerarDacce(){	
	
	$("#resultadoprogresso").html("Gerando a DACC-e...");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarcartadecorrecao/gerardacce/"+idcce+"/idnfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornodacce;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornodacce(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessoDacce"){					
			
			$("#etapaprogresso").html("3/4");		
  			$('#barraprogresso').animate( {width: '80%', opacity: 1, left: '0px' }, function(){
  	  			enviaEmailnfe();
  	  		} );
    	
		}else{
			tratarErros("Erro: "+texto);
			$("#gerarNfe").attr("value","Tentar gerar DACC-e novamente");
			$("#gerarNfe").die('click');
			$("#gerarNfe").live('click', function(){ gerarDacce(); });
		}
	}	
}

/*--------- Enviar email------------------*/
function enviaEmailnfe(){	
	
	$("#resultadoprogresso").html("Enviando e-mail ao cliente...");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarcartadecorrecao/enviaemail/"+idcce+"/idnfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornoemail;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornoemail(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessoemail"){
			$("#etapaprogresso").html("4/4");
			$('#barraprogresso').animate( {width: '100%', opacity: 1,left: '0px'});
			
			$("#resultadoprogresso").html("CC-e gerada com sucesso...");
	  		window.location='/admin/nfe/visualizarnfe/idnfe/'+idnfe;
	  		
		}else{
			tratarErros("Erro: "+texto);
			$("#gerarNfe").attr("value","Enviar e-mail novamente");
			$("#gerarNfe").die('click');
			$("#gerarNfe").live('click', function(){ enviaEmailnfe(); });
		}
	}	
}

function tratarErros(erro){
	$("#resultadoprogresso").css({color:'#f00'}).html(erro);  	
}
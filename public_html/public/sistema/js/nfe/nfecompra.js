/*--- Ordem dos processo -----------------------
 * 1 - Grava dados da venda ---------------- 70
 * 2 - Grava dados da nfe ------------------ 130
 * 3 - Gerar chave nfe --------------------- 220
 * 4 - Gerar XML --------------------------- 240
 * 5 - Assinar XML ------------------------- 250
 * 6 - Validar XML ------------------------- 260
 * 7 - Enviar XML a sefaz ------------------ 320
 * 8 - Veriricar situacao ------------------ 330
 * 9 - Baixar entrada --------------------- 340
 * 
 * */

var xmlHttp;
var ped;
var idnfe;
var estoque;

function continuaFaturamento(nfe,etapa){
	idnfe = nfe;
	if(etapa == 7){		
		retornoSefaz();
	}else if(etapa == 8){		
		gerarDanfe();
	}else if(etapa == 9){		
		gerarBaixaentrada();
	}
}


/*--------- grava dados da nfe ------------------*/
function gerarNfeentrada(str){	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		jAlert("Seu browser não suporta AJAX!","Erro");
		return;
	}
	
	ped = str;	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Salvando dados da entrada de produtos...");
	$("#etapaprogresso").html("1/9");
	
	$('#barraprogresso').animate( { width: '5%'	});
	
	var url="/admin/compras/entradadadosajax/gerarentrada/"+str;
	xmlHttp.onreadystatechange=estadoRetorno;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function estadoRetorno(){
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		texto = unescape(texto);
		texto = texto.split(":");
		
		idnfe = texto[1];
		
		if(texto[0] == "idnfe"){					
			$('#barraprogresso').animate( { width: '10%'	}, function(){
	  			gerarChavenfe();
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar dados da NFe ('+texto+')...');
		}
	}	
}


/*--------- gerar chave nfe ------------------*/
function gerarChavenfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Criando a chave da NFe...");
	$("#etapaprogresso").html("2/9");
			
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfe/gerarchave/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornochave;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornochave(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessochave"){					
			$('#barraprogresso').animate( { width: '20%' }, function(){ 
	  			gerarXmlnfe();
	  		} );
	    	
		}else{
			tratarErros('Erro ao gerar chave da NFe ('+texto+')...');
		}
	}	
}

/*--------- gerar xml nfe ------------------*/
function gerarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Criando XML da NFe...");
	$("#etapaprogresso").html("3/9");
			
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfe/gerarxml/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornoxml;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornoxml(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessoxml"){					
			$('#barraprogresso').animate( { width: '30%' }, function(){
	  			assinarXmlnfe();
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar XML da NFe ('+texto+')...');
		}
	}	
}

/*--------- Assinar xml nfe ------------------*/
function assinarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Assinando XML da NFe...");
	$("#etapaprogresso").html("4/9");
			
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfe/assinarxml/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornoassina;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornoassina(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessoassina"){					
			$('#barraprogresso').animate( { width: '40%' }, function(){
	  			validarXmlnfe();
	  		} );
	    	
		}else{
			tratarErros('Erro ao assinar XML da NFe ('+texto+')...');
		}
	}	
}

/*--------- Validar xml nfe ------------------*/
function validarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Validando xml da NFe...");
	$("#etapaprogresso").html("5/9");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfe/validarxml/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornovalida;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornovalida(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessovalida"){					
			$('#barraprogresso').animate( { width: '50%' }, function(){
	  			enviaXmlsefaz();
	  		} );
	    	
		}else{
			tratarErros('Erro ao validar XML da NFe ('+texto+')...');
		}
	}	
}

/*--------- Enviar a sefaz ------------------*/
function enviaXmlsefaz(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Enviando xml a sefaz...");
	$("#etapaprogresso").html("6/9");

	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfe/enviarxml/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornoenviasefaz;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornoenviasefaz(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessosefaz"){					
			$('#barraprogresso').animate( { width: '60%' }, function(){
	  			retornoSefaz();
	  		} );
	    	
		}else{
			tratarErros('Erro ao enviar XML a sefaz ('+texto+')...');
		}
	}	
}

/*--------- Retorno da sefaz ------------------*/
function retornoSefaz(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Aguardando o retorno da sefaz...");
	$("#etapaprogresso").html("7/9");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfe/retornoxml/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornosefaz;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornosefaz(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessoaprovada"){					
			$('#barraprogresso').animate( { width: '70%' }, function(){
	  			gerarDanfe();
	  		} );
	    	
		}else{
			tratarErros(texto);
			$("#gerarEntrada").live('click', function(){ continuaFaturamento(idnfe,7); });
		}
	}	
}

/*--------- Gerar Danfe ------------------*/
function gerarDanfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Gerando DANFe...");
	$("#etapaprogresso").html("8/9");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfe/gerardanfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornodanfe;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornodanfe(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessodanfe"){					
			$('#barraprogresso').animate( { width: '80%' }, function(){
	  			gerarBaixaentrada();
	  		} );
	    	
		}else{
			tratarErrossefaz("Erro ao gerar a DANFe ("+texto+")...");
			$("#gerarEntrada").live('click', function(){ continuaFaturamento(idnfe,8); });
		}
	}	
}


/*--------- Baixar estoque -----------------*/
/*function gerarBaixaestoque(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Atualizando pedidos de compra...");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/compras/entradadadosajax/baixarestoque/"+ped+"/nfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornobaixa;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornobaixa(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessobaixa"){					
	    	$('#barraprogresso').animate( {
	  		  width: '340px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			$("#titulojanelager").css({color:'#006d6e'}).html('Sucesso!');
	  			$("#subtitulojanelager").css({color:'#006d6e'}).html('Baixa realizada com sucesso! &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; <input type="button" class="greenBtn" onclick="window.location=\'/admin/compras/entradaprodutos/entradaid/'+ped+'\'" >');
	  		} );
	    	
		}else{
			tratarErrossefaz("Erro: "+texto);
		}
	}	
}*/

/*--------- Baixar estoque -----------------*/
function gerarBaixaentrada(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Atualizando os pedidos de compra...");
	$("#etapaprogresso").html("9/9");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/compras/entradadadosajax/baixaentrada/"+ped+"/nfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornoentrada;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornoentrada(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessobaixa"){					
			$('#barraprogresso').animate( { width: '100%' }, function(){
				window.location='/admin/compras/entradaprodutos/entradaid/'+ped;
	  		} );
	    	
		}else{
			tratarErrossefaz("Erro ao finalizar a entrada  ("+texto+")...");
			$("#gerarEntrada").live('click', function(){ continuaFaturamento(idnfe,9); });
		}
	}	
}

function tratarErros(erro){
	$("#resultadoprogresso").css({color:'#f00'}).html(erro);
	$("#gerarGarantia").attr("value","Tentar novamente");
}

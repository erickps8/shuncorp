/*--- Ordem dos processo -----------------------
 * 1 - Grava dados da nfe ------------------ 
 * 2 - Gerar chave nfe --------------------- 
 * 3 - Gerar XML --------------------------- 
 * 4 - Assinar XML ------------------------- 
 * 5 - Validar XML ------------------------- 
 * 6 - Enviar XML a sefaz ------------------ 
 * 7 - Veriricar situacao ------------------ 
 * 8 - Gera DANFe -------------------------- 
 * 9 - Envia Email -------------------------
 * 10 - Baixa estoque ----------------------  * 
 * */

var xmlHttp;
var ped;
var idnfe;
var banco;

function continuaFaturamento(nfe,etapa){
	idnfe = nfe;
	if(etapa == 8){		
		retornoSefaz();
	}else if(etapa == 9){		
		gerarDanfe();
	}else if(etapa == 10){		
		gerarBaixaestoque();
	}else if(etapa == 11){
		enviaEmailnfe();
	}
}

/*--------- grava dados da venda ------------------*/
function gravarDadosnfegarantia(){	
	clientegar = $("input[name=clientegar]").val();
	
	$("#resultadoprogresso").css({color:'#000'}).html("Salvando dados da garantia...");
	$("#etapaprogresso").html("1/10");
	$('#barraprogresso').animate( { width: '5%' });

    $.post('/admin/venda/garantiasentdadosajax', {
    	clientegar: $("input[name=clientegar]").val(),
    	placa: $("input[name=placa]").val(), 
    	frete: $("input[name=frete]").val(), 
    	qtpacote: $("input[name=qtpacote]").val(), 
    	especie: $("#especie option:selected").text(), 
    	tipofrete: $("#tipofrete option:selected").val(), 
    	pesobruto: $("input[name=pesobruto]").val(), 
    	antt: $("input[name=antt]").val(),
    	totalnota: $("input[name=totalnota]").val(),
    	prodsel: $("input[name=prodsel]").val(),
    	prodselneg: $("input[name=prodselneg]").val(),
    	ufplaca: $("#ufplaca option:selected").text(), 
    	obsnfe: $("#obsnfe").val()},    	
    function(resposta) {
		texto = resposta.replace(/^\s+|\s+$/g,"");
		texto = unescape(texto);
				
		texto = texto.split(":");
		
		if(texto[0] == "idnfe"){	
			$('#barraprogresso').animate( { width: '20%' } );
			
			idnfe = texto[1];
			gerarChavenfe();
		}else{
        	tratarErros('Erro ao salvar dados da garantia...');        	
        }
    });	
}


/*--------- gerar chave nfe ------------------*/
function gerarChavenfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Criando a chave da NFe...");
	$("#etapaprogresso").html("2/10");
			
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
			
			$('#barraprogresso').animate( { width: '25%' } , function(){
	  			gerarXmlnfe();
	  		} );
	    	
		}else{
			tratarErros('Erro ao gerar chave da NFe...');
		}
	}	
}

/*--------- gerar xml nfe ------------------*/
function gerarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Criando o XML...");
	$("#etapaprogresso").html("3/10");
			
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
			$('#barraprogresso').animate( { width: '35%' } , function(){
	  			assinarXmlnfe();
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar XML da NFe ('+texto+')...');
		}
	}	
}

/*--------- Assinar xml nfe ------------------*/
function assinarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Assinando o XML...");
	$("#etapaprogresso").html("4/10");
			
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
			$('#barraprogresso').animate( { width: '40%' } , function(){
	  			validarXmlnfe();
	  		} );
	    	
		}else{
			tratarErros('Erro ao assinar XML da NFe...');
		}
	}	
}

/*--------- Validar xml nfe ------------------*/
function validarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Validando o XML...");
	$("#etapaprogresso").html("5/10");
	
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
			$('#barraprogresso').animate( { width: '50%' } , function(){
	  			enviaXmlsefaz();
	  		} );
	    	
		}else{
			tratarErros('Erro ao validar XML da NFe ('+texto+')...');
		}
	}	
}

/*--------- Enviar a sefaz ------------------*/
function enviaXmlsefaz(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Enviando a sefaz...");
	$("#etapaprogresso").html("6/10");

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
			$('#barraprogresso').animate( { width: '65%' } , function(){
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
	$("#etapaprogresso").html("7/10");
	
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
			$('#barraprogresso').animate( { width: '75%' } , function(){
	  			gerarDanfe();
	  		} );
	    	
		}else{
			$("#gerarGarantia").die('click');
			tratarErros(texto);
			
			if(texto == "217"){
				$("#salvarVenda").attr("disabled","disabled");
				$("#gerarGarantia").live('click', function(){ continuaFaturamento(idnfe,8); });
			}else{
				$("#salvarVenda").attr("disabled","");
				$("#gerarGarantia").live('click', function(){ confirmaFaturamento(); });				
			}
			
		}
	}	
}

/*--------- Gerar Danfe ------------------*/
function gerarDanfe(){	
	
	$("#subtitulojanelager").html("NFe aprovada. Gerando a DANFe...");
	$("#resultadoprogresso").css({color:'#000'}).html("Salvando dados da garantia...");
	$("#etapaprogresso").html("8/10");
	
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
			$('#barraprogresso').animate( { width: '85%' } , function(){
	  			enviaEmailnfe();
	  		} );
	    	
		}else{
			tratarErros("Erro: "+texto);
			$("#gerarGarantia").die('click');
			$("#gerarGarantia").live('click', function(){ continuaFaturamento(idnfe,9); });
		}
	}	
}

/*--------- Enviar email------------------*/
function enviaEmailnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Enviando Email para o cliente...");
	$("#etapaprogresso").html("9/10");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfe/enviardanfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornoemail;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornoemail(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessoemail"){					
			$('#barraprogresso').animate( { width: '95%' } , function(){
	  			gerarBaixaestoque();
	  		} );
	    	
		}else{
			tratarErros("Erro: "+texto);
			$("#gerarGarantia").die('click');
			$("#gerarGarantia").live('click', function(){ continuaFaturamento(idnfe,11); });
		}
	}	
}

/*--------- Baixar estoque -----------------*/
function gerarBaixaestoque(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Baixando o estoque...");
	$("#etapaprogresso").html("10/10");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/venda/gravaentregagarantia/idnfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornobaixa;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornobaixa(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessobaixa"){					
			$('#barraprogresso').animate( { width: '100%' } , function(){
	  			$("#resultadoprogresso").css({color:'#000'}).html("Sucesso...");
	  			nfemd5 = $.md5((idnfe).toString());
	  			window.location='/admin/venda/garantiasnfeview/nfe/'+nfemd5;
	  		} );
	    	
		}else{
			tratarErros(texto);
			$("#gerarGarantia").die('click');
			$("#gerarGarantia").live('click', function(){ continuaFaturamento(idnfe,10); });
		}
	}	
}

function tratarErros(erro){
	$("#resultadoprogresso").css({color:'#f00'}).html(erro);
	$("#gerarGarantia").attr("value","Tentar novamente");
}


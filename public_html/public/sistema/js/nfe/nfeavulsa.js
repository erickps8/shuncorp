/*--- Ordem dos processo -----------------------
 * 1 - Grava dados da venda ---------------- 70
 * 2 - Grava dados da nfe ------------------ 130
 * 3 - Grava Financeiro da nfe ------------- 200
 * 4 - Gerar chave nfe --------------------- 220
 * 5 - Gerar XML --------------------------- 240
 * 6 - Assinar XML ------------------------- 250
 * 7 - Validar XML ------------------------- 260
 * 8 - Enviar XML a sefaz ------------------ 320
 * 9 - Veriricar situacao ------------------ 330
 * 10 - Envia email ------------------------ 340
 * 
 * */

var xmlHttp;
var ped;
var idnfe;


function continuaFaturamento(nfe,etapa){
	idnfe = nfe;
	if(etapa == 8){		
		retornoSefaz();
	}else if(etapa == 9){		
		gerarDanfe();
	}else if(etapa == 10){
		gerarBaixa();
	}
}

/*--------- grava dados da nfe ------------------*/
function gerarNfe(str){
	xmlHttp=GetXmlHttpObject();
	ped = str;
	
	$("#resultadoprogresso").css({color:'#000'}).html("Salvando dados da NF-e...");
	$("#etapaprogresso").html("1/9");
	$('#barraprogresso').animate( { width: '5%' });
	
	var url="/admin/nfe/gerarnfeavulsa/nfe/"+str;
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
			
			$('#barraprogresso').animate( { width: '10%' } , function(){
	  			gerarChavenfe();
	  			
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar dados da NFe...');
		}
	}	
}


/*--------- gerar chave nfe ------------------*/
function gerarChavenfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Criando chave da NF-e...");
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
	
	$("#resultadoprogresso").css({color:'#000'}).html("Criando XML...");
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
			$('#barraprogresso').animate( { width: '30%' } , function(){
	  			assinarXmlnfe();
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar XML da NFe...');
		}
	}	
}

/*--------- Assinar xml nfe ------------------*/
function assinarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Assinando XML...");
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
			$('#barraprogresso').animate( { width: '35%' } , function(){
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
			$('#barraprogresso').animate( { width: '50%' } , function(){
	  			enviaXmlsefaz();
	  		} );
	    	
		}else{
			tratarErros('Erro ao validar XML da NFe...');
		}
	}	
}

/*--------- Enviar a sefaz ------------------*/
function enviaXmlsefaz(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Enviando a sefaz...");
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
			$('#barraprogresso').animate( { width: '60%' } , function(){
	  			retornoSefaz();
	  		} );
	    	
		}else{
			tratarErros('Erro ao enviar XML a sefaz...');
		}
	}	
}

/*--------- Retorno da sefaz ------------------*/
function retornoSefaz(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Retorno da sefaz NF-e...");
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
			$('#barraprogresso').animate( { width: '80%' } , function(){
	  			gerarDanfe();
	  		} );
	    	
		}else{
			tratarErros(texto);
			$("#gerarNfe").die('click');
			$("#gerarNfe").live('click', function(){ continuaFaturamento(idnfe,8); });
		}
	}	
}

/*--------- Gerar Danfe ------------------*/
function gerarDanfe(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Gerando Danfe...");
	$("#resultadoprogresso").css({color:'#000'}).html("Gerando Danfe...");
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
			
			$('#barraprogresso').animate( { width: '90%' }, function(){
				gerarBaixa();
			});
	    	
		}else{
			tratarErros(texto);
			$("#gerarNfe").die('click');
			$("#gerarNfe").live('click', function(){ continuaFaturamento(idnfe,9); });
		}
	}	
}

/*--------- Baixar estoque -----------------*/
function gerarBaixa(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Atualizando o estoque...");
	$("#etapaprogresso").html("9/9");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfeavulsa/baixarestoque/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornobaixa;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornobaixa(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessobaixa"){					
			$('#barraprogresso').animate( { width: '100%' }, function(){
				nfemd5 = $.md5((ped).toString());
				window.location='/admin/nfe/visualizarnfe/nfe/'+nfemd5+'/tipo/avulsa';
	  		} );
	    	
		}else{
			tratarErrossefaz("Erro ao finalizar a entrada  ("+texto+")...");
			$("#gerarEntrada").live('click', function(){ continuaFaturamento(idnfe,10); });
		}
	}	
}

function tratarErros(erro){
	$("#resultadoprogresso").css({color:'#f00'}).html(erro);
	$("#gerarNfe").attr("value","Tentar novamente");
}
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
 * 10 - Gerar Danfe ------------------------
 * 11 - Baixa estoque ----------------------
 * 12 - Envia email ------------------------ 340
 * 
 * */

var xmlHttp;
var ped;
var idnfe;
var banco;

function etapasFaturamento(nfe, etp){
	
	ped = $("input[name=ped]").val();
	
	$('input[name=vervalida]').val(0);
	
	if((etp == 8)){
		$('#divnfe').css({display:'block'});
		$("#resultadoprogresso").html("Aguardando retorno da sefaz...");
		$("#etapaprogresso").html("9/12");
		$('#barraprogresso').css({width: '70%'});
		$("#gerarVenda").live('click', function(){ continuaFaturamento(nfe,etp); });
		$("#salvarVenda").attr("disabled","disabled");
				
	}else if((etp == 9)){  
		$('#divnfe').css({display:'block'});
		$("#resultadoprogresso").html("Aguardando geração da DANFe...");
		$("#etapaprogresso").html("9/12");
		$('#barraprogresso').css({width: '85%'});
		$("#gerarVenda").live('click', function(){ continuaFaturamento(nfe,etp); });
		$("#salvarVenda").attr("disabled","disabled");		
	}else if(etp == 10){  	
		$('#divnfe').css({display:'block'});
  		$("#resultadoprogresso").html("Aguardando baixa no estoque...");
  		$("#etapaprogresso").html("11/12");
  		$('#barraprogresso').css({width: '90%'});		
  		$("#gerarVenda").live('click', function(){ continuaFaturamento(nfe,etp); });
		$("#salvarVenda").attr("disabled","disabled");
	}else if(etp == 11){  
		$('#divnfe').css({display:'block'});
		$("#resultadoprogresso").css({color:'#000'}).html("Enviando Email para o cliente...");
  		$("#etapaprogresso").html("12/12");
  		$('#barraprogresso').css({width: '95%'});		
  		$("#gerarVenda").live('click', function(){ continuaFaturamento(nfe,etp); });
		$("#salvarVenda").attr("disabled","disabled");
	}else{
		//$("#gerarVenda").live('click', function(){ confirmaFaturamento(); });
		$('input[name=vervalida]').val(1);
	}
}

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
function gravarDadosnfevenda(){	
	ped = $("input[name=ped]").val();
	
	$("#resultadoprogresso").css({color:'#000'}).html("Salvando dados da venda...");
	$("#etapaprogresso").html("1/12");
	
	$('#barraprogresso').animate( {
	  width: '5%',
	  opacity: 1,
	  left: '0px'
	});
	
    $.post('/admin/venda/pedidosentdadosajax', {
    	ped: 			$("input[name=ped]").val(), 
    	pedcli: 		$("input[name=pedcli]").val(), 
    	placa: 			$("input[name=placa]").val(), 
    	frete: 			$("input[name=frete]").val(), 
    	seguro: 		$("input[name=seguro]").val(), 
    	desconto: 		$("input[name=desconto]").val(), 
    	descontoperc: 	$("input[name=descontoperc]").val(), 
    	prazo1: 		$("input[name=prazo1]").val(), 
    	vlprazo1: 		$("input[name=vlprazo1]").val(), 
    	dataprazo1: 	$("input[name=dataprazo1]").val(), 
    	prazo2: 		$("input[name=prazo2]").val(), 
    	vlprazo2: 		$("input[name=vlprazo2]").val(), 
    	dataprazo2: 	$("input[name=dataprazo2]").val(), 
    	prazo3: 		$("input[name=prazo3]").val(), 
    	vlprazo3: 		$("input[name=vlprazo3]").val(), 
    	dataprazo3: 	$("input[name=dataprazo3]").val(), 
    	prazo4: 		$("input[name=prazo4]").val(), 
    	vlprazo4: 		$("input[name=vlprazo4]").val(), 
    	dataprazo4: 	$("input[name=dataprazo4]").val(), 
    	prazo5: 		$("input[name=prazo5]").val(), 
    	vlprazo5: 		$("input[name=vlprazo5]").val(), 
    	dataprazo5: 	$("input[name=dataprazo5]").val(), 
    	qtpacote: 		$("input[name=qtpacote]").val(), 
    	especie: 		$("#especie option:selected").text(), 
    	tipofrete: 		$("#tipofrete option:selected").val(), 
    	pesobruto: 		$("input[name=pesobruto]").val(), 
    	antt: 			$("input[name=antt]").val(), 
    	ufplaca: 		$("#ufplaca option:selected").text(), 
    	obs: 			$("#obs").val(), 
    	obsnfe: 		$("#obsnfe").val(), 
    	email: 			$("input[name=email]").val(), 
    	contatoemail: 	$("input[name=contatoemail]").val(), 
    	totalnota: 		$("input[name=totalnota]").val(),
    	trans: 			$("#trans option:selected").val()
    }, 
    function(resposta) {
        if (resposta != false) {
        	tratarErros('Erro ao salvar dados da venda...');
        }else {
        	$('#barraprogresso').animate( {
      		  width: '10%',
      		  opacity: 1,
      		  left: '0px'
      		} );        	
        	        	
        	gerarNfevenda(ped, $("#trans option:selected").val());        	        	
        }
    });	
}

/*--------- grava dados da nfe ------------------*/
function gerarNfevenda(str, transp){	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		alert ("Seu browser não suporta AJAX!");
		return;
	}
	
	$("#resultadoprogresso").css({color:'#000'}).html("Gerando dados da NFe..."); 
	$("#etapaprogresso").html("2/12");
	$("#salvarVenda").attr("disabled","disabled");
	
	var url="/admin/venda/pedidosentdadosajax/gerarvenda/true/ped/"+str+"/trans/"+transp;
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
	    	$('#barraprogresso').animate( {
	  		  width: '20%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			gerarFinanceirovenda(ped);
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar dados da NFe...');
		}
	}	
}

function gerarFinanceirovenda(str){		
	$("#resultadoprogresso").css({color:'#000'}).html("Gerando financeiro da Venda...");
	$("#etapaprogresso").html("3/12");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/venda/pedidosentdadosajax/financeirovenda/true/ped/"+str+"/banco/"+$("input[name=bancofin]").val();
	xmlHttp.onreadystatechange=estadoRetornofin;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);	
}

function estadoRetornofin(){
	if (xmlHttp.readyState==4){		
		var textof = xmlHttp.responseText;
		textof = textof.replace(/^\s+|\s+$/g,"");
		
		if(textof=="sucessof"){
			$('#barraprogresso').animate({
			  width: '30%',
			  opacity: 1,
			  left: '0px'
			}, function(){
				gerarChavenfe();
			});
		}else{
			tratarErros('Erro ao gerar o financeiro...');
		}		
	}	
}

/*--------- gerar chave nfe ------------------*/
function gerarChavenfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Criando chave...");
	$("#etapaprogresso").html("4/12");
	
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
	    	$('#barraprogresso').animate( {
	  		  width: '35%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			gerarXmlnfe();
	  		} );
	    	
		}else{
			tratarErros('Erro ao gerar chave da NFe...');
		}
	}	
}

/*--------- gerar xml nfe ------------------*/
function gerarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Criando xml...");
	$("#etapaprogresso").html("5/12");
	
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
	    	$('#barraprogresso').animate( {
	  		  width: '50%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			assinarXmlnfe();
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar XML da NFe ('+texto+')...');
		}
	}	
}

/*--------- Assinar xml nfe ------------------*/
function assinarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Assinando xml...");
	$("#etapaprogresso").html("6/12");
			
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
	    	$('#barraprogresso').animate( {
	  		  width: '55%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			validarXmlnfe();
	  		} );
	    	
		}else{
			tratarErros('Erro ao assinar XML da NFe...');
		}
	}	
}

/*--------- Validar xml nfe ------------------*/
function validarXmlnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Validando xml...");
	$("#etapaprogresso").html("7/12");
	
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
	    	$('#barraprogresso').animate( {
	  		  width: '60%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
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
	$("#etapaprogresso").html("8/12");
	
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
	    	$('#barraprogresso').animate( {
	  		  width: '70%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			retornoSefaz();
	  		} );
	    	
		}else{
			tratarErros('Erro ao enviar XML a sefaz ('+texto+')...');
		} 
	}	
}

/*--------- Retorno da sefaz ------------------*/
function retornoSefaz(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Aguardando retorno da sefaz...");
	$("#etapaprogresso").html("9/12");
	
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
	    	$('#barraprogresso').animate( {
	  		  width: '80%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			gerarDanfe();
	  		} );
	    	
		}else{
			$("#gerarVenda").die('click');
			tratarErros(texto);
					
			texto = texto.split("|");
			
			if(texto[0] == "217"){
				$("#salvarVenda").attr("disabled","disabled");
				$("#gerarVenda").live('click', function(){ continuaFaturamento(idnfe,8); });				
			}else{
				jAlert("NFe reprovada: "+texto[1]+". Corrija possíveis erros, e tente novamente!", "Erro!", function(){
					window.location.reload(true);
				});			
			}
		}
	}	
}

/*--------- Gerar Danfe ------------------*/
function gerarDanfe(){
	
	$("#resultadoprogresso").css({color:'#000'}).html("Gerando Danfe...");
	$("#etapaprogresso").html("10/12");
	
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
	    	$('#barraprogresso').animate( {
	  		  width: '85%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			gerarBaixaestoque();
	  		} );
	    	
		}else{
			tratarErros("Erro: "+texto);
			$("#gerarVenda").die('click');
			$("#gerarVenda").live('click', function(){ continuaFaturamento(idnfe,9); });
		}
	}	
}

/*--------- Baixar estoque -----------------*/
function gerarBaixaestoque(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Baixando estoque...");
	$("#etapaprogresso").html("11/12");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/venda/pedidosentdadosajax/baixarestoque/"+idnfe;
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
	  		  width: '90%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			enviaEmailnfe();
	  		} );
	    	
		}else{
			tratarErros(texto);
			$("#gerarVenda").die('click');
			$("#gerarVenda").live('click', function(){ continuaFaturamento(idnfe,10); });			
		}
	}	
}

/*--------- Gerar Danfe ------------------*/
function enviaEmailnfe(){	
	
	$("#resultadoprogresso").css({color:'#000'}).html("Enviando Email para o cliente...");
	$("#etapaprogresso").html("12/12");
	
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
	    	$('#barraprogresso').animate( {
	  		  width: '100%',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			$("#resultadoprogresso").css({color:'#006d6e'}).html('Venda realizada com sucesso!');
	  			window.location='/admin/venda/pedidosvenda/idped/'+ped;
	  		} );
	    	
		}else{
			tratarErros("Erro: "+texto);
			$("#gerarVenda").die('click');
			$("#gerarVenda").live('click', function(){ continuaFaturamento(idnfe,11); });
		}
	}	
}

function tratarErros(erro){
	$("#resultadoprogresso").css({color:'#f00'}).html(erro);
	$("#gerarVenda").attr("value","Tentar novamente");
	$('input[name=vervalida]').val(0);
}

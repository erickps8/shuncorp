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
var banco;

/*--------- grava dados da venda ------------------*/
function cancelarGarantia(){	
	ped   = $("input[name=ped]").val();
	idnfe = $("input[name=idnfe]").val();
	
	$("#subtitulojanelager").html("Salvando motivo do cancelamento...");
	
	$('#barraprogresso').animate( {
	  width: '50px',
	  opacity: 1,
	  left: '0px'
	} );
    $.post('/admin/venda/garantiasremover', {
    	ped: $("input[name=ped]").val(), 
    	nfe: $("input[name=idnfe]").val(), 
    	obscancela: $("#obscancela").val()}, 
    	
    function(resposta) {
		texto = resposta.replace(/^\s+|\s+$/g,"");
		texto = unescape(texto);
		
		if(texto == "sucessogravamotivo"){	
			$('#barraprogresso').animate( {
	      		  width: '70px',
	      		  opacity: 1,
	      		  left: '0px'
	      		} );
			
			gerarCancelamento();
		}else{
        	tratarErros('Erro ao salvar motivo...');
        }
    		
    });	
}

/*--------- Gerar XML ------------------*/
function gerarCancelamento(){	
	
	$("#subtitulojanelager").html("Cancelando Garantia - Enviando a sefaz...");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/cancelarnfe/idnfe/"+idnfe;
	xmlHttp.onreadystatechange=estadoRetornocancelamento;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function estadoRetornocancelamento(){	
	if (xmlHttp.readyState==4){		
		var texto = xmlHttp.responseText;
		texto = texto.replace(/^\s+|\s+$/g,"");
		
		if(texto == "sucessocancela"){					
	    	$('#barraprogresso').animate( {
	  		  width: '315px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			gerarAjustestoque();
	  		} );
	    	
		}else{
			tratarErros("Erro: Ao enviar dados a sefaz");
		}
	}	
}

/*--------- Baixar estoque -----------------*/
function gerarAjustestoque(){	
	
	$("#subtitulojanelager").html("Cancelando Garantia - Ajustando estoque...");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/venda/garantiasremover/garantianfe/"+idnfe;
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
	  			$("#subtitulojanelager").css({color:'#006d6e'}).html('Cancelado com sucesso! &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; <a class="botaoconfirma" href="javascript:void(0)" onclick="window.location=\'/admin/venda/garantiasnfe\'" > &nbsp; Ok &nbsp; </a>');
	  		} );
	    	
		}else{
			tratarErros("Erro: "+texto);
		}
	}	
}

function tratarErros(erro){
	$("#titulojanelager").css({color:'#f00'}).html('Erro!');
	$("#subtitulojanelager").css({color:'#f00'}).html(erro+' &nbsp;  &nbsp; <a class="botaocancela" href="javascript:void(0)" onclick=\'$("#mascara").hide(); $("#jmodal").remove();\' > &nbsp; Cancelar &nbsp; </a>');  	
}

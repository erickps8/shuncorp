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
var idnfe;

/*--------- grava dados da venda ------------------*/
function gravarDadosnfe(){	
	
	$("#subtitulojanelager").html("Salvando dados da NF-e...");
	
	$('#barraprogresso').animate( {
	  width: '50px',
	  opacity: 1,
	  left: '0px'
	} );
	
    $.post('/admin/nfe/gerarnfecomplementar', {
    	nfe: 		$("input[name=idnfe]").val(),
    	descricao:	$("input[name=descricao]").val(),
    	icms: 		$("input[name=icms]").val(), 
    	icmsst: 	$("input[name=icmsst]").val(), 
    	pis: 		$("input[name=pis]").val(), 
    	cofins: 	$("input[name=cofins]").val(), 
    	ipi: 		$("input[name=ipi]").val(), 
    	obs: 		$("#obs").val()}, 
    	
    function(resposta) {
    		    		
		texto = resposta.replace(/^\s+|\s+$/g,"");
		texto = unescape(texto);
		texto = texto.split(":");
    	
		if(texto[0] != "sucessodados"){
    		tratarErros('Erro ao salvar dados da NF-e...');
        }else {
        	$('#barraprogresso').animate( {
      		  width: '130px',
      		  opacity: 1,
      		  left: '0px'
      		} );        	
        	        	
        			
    		idnfe = texto[1];
    		
        	gerarChavenfe();
        	
        }
    });	
}

/*--------- gerar chave nfe ------------------*/
function gerarChavenfe(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Criando chave...");
			
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
	  		  width: '220px',
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
	
	$("#subtitulojanelager").html("Gerando NFe - Criando xml...");
			
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
	  		  width: '240px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			assinarXmlnfe();
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar XML da NFe...');
		}
	}	
}

/*--------- Assinar xml nfe ------------------*/
function assinarXmlnfe(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Assinando xml...");
			
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
	  		  width: '250px',
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
	
	$("#subtitulojanelager").html("Gerando NFe - Validando xml...");
	
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
	  		  width: '250px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			enviaXmlsefaz();
	  			//tratarErros('Bloq...');
	  		} );
	    	
		}else{
			tratarErros('Erro ao validar XML da NFe...');
		}
	}	
}

/*--------- Enviar a sefaz ------------------*/
function enviaXmlsefaz(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Enviando a sefaz...");

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
	  		  width: '280px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			retornoSefaz();
	  		} );
	    	
		}else{
			tratarErros('Erro ao enviar XML a sefaz...');
		}
	}	
}

/*--------- Retorno da sefaz ------------------*/
function retornoSefaz(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Retorno da sefaz...");
	
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
	  		  width: '305px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			gerarDanfe();
	  		} );
	    	
		}else{
			alert(texto);
			tratarErros(texto.substr(0,25));
		}
	}	
}

/*--------- Gerar Danfe ------------------*/
function gerarDanfe(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Gerando Danfe...");
	
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
	  		  width: '315px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			enviaEmailnfe();
	  		} );
	    	
		}else{
			tratarErrossefaz("Erro: "+texto);
		}
	}	
}


/*--------- Gerar Danfe ------------------*/
function enviaEmailnfe(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Enviando Email...");
	
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
	  		  width: '340px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			$("#titulojanelager").css({color:'#006d6e'}).html('Sucesso!');
	  			$("#subtitulojanelager").css({color:'#006d6e'}).html('NF-e realizada com sucesso! &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; <input class="greenBtn" type="button" value="Ok" onclick="window.location=\'/admin/nfe/visualizarnfe/idnfe/'+idnfe+'\'" >');
	  		} );
	    	
		}else{
			tratarErrossefaz("Erro: "+texto);
		}
	}	
}

function tratarErros(erro){
	$("#titulojanelager").css({color:'#f00'}).html('Erro!');
	$("#subtitulojanelager").css({color:'#f00'}).html(erro+' &nbsp;  &nbsp; <input type="button" value="Cancelar" class="redBtn" onclick=\'$("#mascara").hide(); $("#jmodal").remove();\' >');  	
}

function tratarErrossefaz(erro){
	$("#titulojanelager").css({color:'#f00'}).html('Erro!');
	$("#subtitulojanelager").css({color:'#f00'}).html(erro+' &nbsp;  &nbsp; <input type="button" value="Cancelar" class="redBtn" onclick="window.location=\'/admin/nfe/visualizarnfe/idnfe/'+idnfe+'\'" >');
}
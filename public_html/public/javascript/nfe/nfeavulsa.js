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

/*--------- grava dados da nfe ------------------*/
function gerarNfe(str){	
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
		jAlert('Seu browser não suporta AJAX!', 'Alerta');
		return;
	}
	
	ped = str;
	
	$("#subtitulojanelager").html("Gerando dados da NFe..."); 
	
	$('#barraprogresso').animate( {
	  width: '50px',
	  opacity: 1,
	  left: '0px'
	} );
	
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
	    	$('#barraprogresso').animate( {
	  		  width: '80px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			//gerarFinanceirovenda(ped);
	  			gerarChavenfe();
	  			
	  		} );	    	
		}else{
			tratarErros('Erro ao gerar dados da NFe...');
		}
	}	
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
	  		  width: '130px',
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
	  		  width: '160px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			//$("#subtitulojanelager").html("Gerando NFe - Criando chave...");
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
	  		  width: '190px',
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
	  		  width: '210px',
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
	  		  width: '250px',
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
	  		  width: '290px',
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
			tratarErrossefaz("Erro: "+texto.substr(0,45));
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
	  		  width: '330px',
	  		  opacity: 1,
	  		  left: '0px'
	  		}, function(){
	  			gerarBaixaestoque();
	  		} );
	    	
		}else{
			tratarErrossefaz("Erro: "+texto);
		}
	}	
}

/*--------- Baixar estoque -----------------*/
function gerarBaixaestoque(){	
	
	$("#subtitulojanelager").html("Gerando NFe - Finalizando NFe...");
	
	xmlHttp=GetXmlHttpObject();
	var url="/admin/nfe/gerarnfeavulsa/baixarestoque/"+ped+"/idnfe/"+idnfe;
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
	  			$("#subtitulojanelager").css({color:'#006d6e'}).html('NFe gerada com sucesso! &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; <a class="botaoconfirma" href="javascript:void(0)" onclick="window.location=\'/admin/nfe/visualizarnfe/idnfe/'+idnfe+'\'" > &nbsp; Ok &nbsp; </a>');
	  		} );
	    	
		}else{
			tratarErrossefaz("Erro: "+texto);
		}
	}	
}

function tratarErros(erro){
	$("#titulojanelager").css({color:'#f00'}).html('Erro!');
	$("#subtitulojanelager").css({color:'#f00'}).html(erro+' &nbsp;  &nbsp; <input type="button" class="redBtn"  value="Cancelar" onclick=\'$("#mascara").hide(); $("#jmodal").remove();\'">');  	
}

function tratarErrossefaz(erro){
	$("#titulojanelager").css({color:'#f00'}).html('Erro!');
	$("#subtitulojanelager").css({color:'#f00'}).html(erro+' &nbsp;  &nbsp; <input type="button" class="redBtn"  value="Cancelar" onclick="window.location=\'/admin/nfe/visualizarnfe/idnfe/'+idnfe+'\'">');
}
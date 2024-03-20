var xmlHttp;
var tipo;
var clientes;
var frel;
var buscagrupo;
var subgrupo;
var prod;
var periodo;

/*--------- grava dados do contatos ------------------*/
function gerarRelatorio(){	
	document.getElementById('carregarGraficos').style.display = "block";
	
	tiporel     	= $("select[name=tipo] option:selected").val();
	periodorel  	= parseInt($("select[name=periodo] option:selected").val());
	clientesrel 	= $("select[name=clientes] option:selected").val();
	frelrel			= $("select[name=frel] option:selected").val();
	buscagruporel	= $("select[name=buscagrupo] option:selected").val();
	subgruporel		= $("select[name=subgrupo] option:selected").val();
	prodrel			= $("input[name=prod]").val();
	
	
    $.post('/admin/venda/buscarelatoriogarantia', {
    	tipo: 		$("select[name=tipo] option:selected").val(),
    	clientes: 	$("select[name=clientes] option:selected").val(),
    	frel: 		$("select[name=frel] option:selected").val(),
    	buscagrupo: $("select[name=buscagrupo] option:selected").val(),
    	subgrupo: 	$("select[name=subgrupo] option:selected").val(),
    	prod:	 	$("input[name=prod]").val(),
    	periodo:	$("select[name=periodo] option:selected").val()},
    function(resposta) {
		
		texto = resposta.replace(/^\s+|\s+$/g,"");
		texto = unescape(texto);
		
		if(texto == "erro"){	
			jAlert('Erro ao gerar o relatório! Tente novamente.', 'Erro!');
			document.getElementById('carregarGraficos').style.display = "none";
		}else{
			
			document.getElementById('imprimeGrafico').setAttribute('href',"/admin/venda/garantiasrelimp/tipo/"+tiporel+"/clientes/"+clientesrel+"/frel/"+frelrel+"/buscagrupo/"+buscagruporel+"/subgrupo/"+subgruporel+"/prod/"+prodrel+"/periodo/"+periodorel);
			document.getElementById('imprimeHistorico').setAttribute('href',"/admin/venda/garantiasrelimp/tipo/"+tiporel+"/clientes/"+clientesrel+"/frel/"+frelrel+"/buscagrupo/"+buscagruporel+"/subgrupo/"+subgruporel+"/prod/"+prodrel+"/periodo/"+periodorel);
			
			if(tiporel != 3){
				//naopagoscli|pagoscli|vendascli|vendasztl|pagosztl|cortesia
				
				texto = texto.split("|");
				
				var data = [];
				data[0] = { label: "Não procedente", data: parseInt(texto[0]) };
				data[1] = { label: "Procedente", data: parseInt(texto[1]) };
				data[2] = { label: "Cortesia", data: parseInt(texto[5]) };
				
				var data2 = [];
				data2[0] = { label: "Vendas", data: parseInt(texto[3]) };
				data2[1] = { label: "Procedente", data: parseInt(texto[4]) };			
				
				var data3 = [];
				data3[0] = { label: "Compras", data: parseInt(texto[2]) };
				data3[1] = { label: "Procedente", data: parseInt(texto[1]) };
				
				exibeRelatorio(data,data2,data3);
				
				listaRelatorio();
			}else{
				
				texto = texto.split("-");
				
				var d1 = [];
				var d2 = [];
				var d3 = [];
				data = new Date();
				
				var ticks = [];
				
				for (var i = periodorel; i > 0; i -= 1){
					ticks.push([i,((data.getMonth()+1)+"/"+data.getFullYear())]);
					
					//--- dados das devolucoes em garantis --------------------------
					
					var dp1 = 0;
					arrTexto = texto[0].split("|");
					for (var j = 0; j < arrTexto.length; j += 1){
						dadosArray = arrTexto[j].split(":");
						
						if(dadosArray[1] == ((data.getMonth()+1)+"/"+data.getFullYear())){
							dp1 = dadosArray[0];
						}
					}
					
					d1.push([i, dp1]);
					
					//--- dados das garantias pagas --------------------------
					var dp2 = 0;
					arrTexto = texto[1].split("|");
					for (var j = 0; j < arrTexto.length; j += 1){
						dadosArray = arrTexto[j].split(":");
						
						if(dadosArray[1] == ((data.getMonth()+1)+"/"+data.getFullYear())){
							dp2 = dadosArray[0];
						}
					}
					
					d2.push([i, dp2]);
					
					//--- dados das vendas --------------------------
					var dp3 = 0;
					arrTexto = texto[2].split("|");
					for (var j = 0; j < arrTexto.length; j += 1){
						dadosArray = arrTexto[j].split(":");
						
						if(dadosArray[1] == ((data.getMonth()+1)+"/"+data.getFullYear())){
							dp3 = dadosArray[0];
						}
					}
					
					d3.push([i, dp3]);
					
					data.setMonth(data.getMonth() - 1);
				}
				
				exibeRelatoriogeral(d1,d2,d3,ticks,periodorel);
			}
        }
    });	
}

function listaRelatorio(){
	document.getElementById('dadosGarantias').style.display = "block";
    $.post('/admin/venda/buscalistarelatoriogarantia', {
    	tipo: 		$("select[name=tipo] option:selected").val(),
    	clientes: 	$("select[name=clientes] option:selected").val(),
    	frel: 		$("select[name=frel] option:selected").val(),
    	buscagrupo: $("select[name=buscagrupo] option:selected").val(),
    	subgrupo: 	$("select[name=subgrupo] option:selected").val(),
    	prod:	 	$("input[name=prod]").val(),
    	periodo:	$("select[name=periodo] option:selected").val()},
    function(resposta) {
			
		if(resposta == "erro"){	
			jAlertreload('Erro ao lista os produtos do relatório! Tente novamente.', 'Erro!');
			document.getElementById('dadosGarantias').style.display = "none";
		}else{
			document.getElementById('dadosGarantias').innerHTML=resposta;			
        }
		
    });	
}

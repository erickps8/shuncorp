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
	subgruporel		= $("select[name=buscagruposub] option:selected").val();
	prodrel			= $("input[name=prod]").val();
	
	
    $.post('/admin/venda/buscarelatoriogarantia', {
    	tipo: 			$("select[name=tipo] option:selected").val(),
    	clientes: 		$("select[name=clientes] option:selected").val(),
    	frel: 			$("select[name=frel] option:selected").val(),
    	buscagrupo: 	$("select[name=buscagrupo] option:selected").val(),
    	buscagruposub: 	$("select[name=buscagruposub] option:selected").val(),
    	prod:	 		$("input[name=prod]").val(),
    	periodo:		$("select[name=periodo] option:selected").val()},
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
					
					//--- dados das devolucoes em garantias --------------------------
					
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

var on 	= 0;
var qtt = 0;
var vl 	= 0;
var imp = 0;
var icm = 0;
var tc = "";

function buscaProdutos(){
	
	var cod 	= $('#codigo').val();
  	var qt 		= $('#qt').val();
	var valor 	= $('#valor').val();
	var cli 	= $('input[name=idcliente]').val();
	  
	qtt = qt;
	vl 	= valor;
	
  	if(cod==''){
    	$('#resultado').html('Digite o c&oacute;digo!');
    	return;
 	}else if(qt==''){
 		$('#resultado').html('Digite a quantidade!');
  		return;
  	}else{
  		
  		$.ajax({
  		  url: "/admin/venda/buscaprodutogarantia/q/"+cod+"/qt/"+qt+"/vl/"+valor+"/cliente/"+cli,
  		  success: function(data) {
  			  
  			var texto = data;
  			texto = texto.replace(/\+/g," ");
  			texto = unescape(texto);
  			texto = texto.split("|");
  			
  			var arrId = $('#arrId').val();
  			
  			narrId = arrId.split(";");
  			erro = 0;
  			for(i=0;i < narrId.length; i++){
  				if(texto[0]==narrId[i]){
  					erro = 1;
  				}
  			}

  			if(texto[6]=='erro2'){
  				$('#resultado').html('Produto com preço superior ao maior preço de venda: R$ '+texto[7]);
  			    return false;
  			}else if(erro==1){
  				$('#resultado').html('Este código já esta na lista');
  			    return false;
  			    
  			}else if(texto[0]=="erro1"){
  				$('#resultado').html('Código incorreto');
  				$('#codigo').focus();
  			}else{
  				
  				if(texto[6]=='erro3'){
  					
  					jConfirm('Não existe venda deste produto para este cliente! Deseja incluir mesmo assim?', 'Confirme', function(r) {

  						if(r==true){
  							
  							document.getElementById('arrId').value += texto[0]+";";
  							var tbCod = document.getElementById("txtHint").insertRow(2);
  					
  							var y= tbCod.insertCell(0);
  							var z= tbCod.insertCell(1);
  							var b= tbCod.insertCell(2);
  							var e= tbCod.insertCell(3);
  							var g= tbCod.insertCell(4);
  					
  							y.align = "center";
  							y.setAttribute("class","td_orc");
  							y.innerHTML=texto[1];
  							z.align = "center";
  							z.setAttribute("class","td_orc");
  							z.innerHTML=' '+qtt+'<input type="hidden" name="'+texto[0]+'" value="'+qtt+'">';
  							b.align = "left";
  							b.setAttribute("class","td_orc");
  							b.innerHTML=texto[4]+'<input type="hidden" name="valor_'+texto[0]+'" value="'+texto[2]+'">';
  							e.align = "left";
  							e.setAttribute("class","td_orc");
  							e.innerHTML=texto[3];
  							g.align = "center";
  							g.setAttribute("class","td_orc");
  							g.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+texto[0]+')"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="15"  border="0"></a>';
  						
  						}
  						
  					});				
  				}else{

  					document.getElementById('arrId').value += texto[0]+";";
  					var tbCod = document.getElementById("txtHint").insertRow(2);
  			
  					var y= tbCod.insertCell(0);
  					var z= tbCod.insertCell(1);
  					var b= tbCod.insertCell(2);
  					var e= tbCod.insertCell(3);
  					var g= tbCod.insertCell(4);
  			
  					y.align = "center";
  					y.setAttribute("class","td_orc");
  					y.innerHTML=texto[1];
  					z.align = "center";
  					z.setAttribute("class","td_orc");
  					z.innerHTML=' '+qtt+'<input type="hidden" name="'+texto[0]+'" value="'+qtt+'">';
  					b.align = "left";
  					b.setAttribute("class","td_orc");
  					b.innerHTML=texto[4]+'<input type="hidden" name="valor_'+texto[0]+'" value="'+texto[2]+'">';
  					e.align = "left";
  					e.setAttribute("class","td_orc");
  					e.innerHTML=texto[3];
  					g.align = "center";
  					g.setAttribute("class","td_orc");
  					g.innerHTML='<a href="#" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+texto[0]+')"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="15"  border="0"></a>';
  				
  				}	
  				
  				$('#codigo').val("");
  		  	  	$('#qt').val("");
  		  		$('#valor').val("");
  		  		$('#codigo').focus();
  				
  			}
  		  }
  		});
  		
  		$('#codigo').val("");
  	  	$('#qt').val("");
  		$('#valor').val("");
    }	
}


function float2moeda(num) {

   x = 0;

   if(num<0) {
      num = Math.abs(num);
      x = 1;
   }
   if(isNaN(num)) num = "0";
      cents = Math.floor((num*100+0.5)%100);

   num = Math.floor((num*100+0.5)/100).toString();

   if(cents < 10) cents = "0" + cents;
      for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
         num = num.substring(0,num.length-(4*i+3))+'.'
               +num.substring(num.length-(4*i+3));
      ret = num + ',' + cents;
      if (x == 1) ret = ' - ' + ret;return ret;

}

function moeda2float(moeda){
   moeda = moeda.replace(".","");
   moeda = moeda.replace(",",".");
   return parseFloat(moeda);
}
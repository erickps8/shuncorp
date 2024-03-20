$(function(){
	
	buscaRegistro(1);
	
	$('#bntMaisfiltros').click(function(){
		$('#avancado').toggle('slow');
		$('#btnPesq1').toggle('slow');
	});
	
	$('#pesqmedida').change(function(){
		$('.bmed').hide();
		
		if($('#pesqmedida option:selected').val() == 1){
			$('#buscrol').show();			
		}else if($('#pesqmedida option:selected').val() == 2){
			$('#buschom').show();			
		}else if($('#pesqmedida option:selected').val() == 3){
			$('#buscsli').show();			
		}else if($('#pesqmedida option:selected').val() == 4){
			$('#busccru').show();			
		}		
	});
	
	//-- Ppaginacao ------------------------------------------------
	$('.btnPaginator').live('click', function() {
		buscaRegistro($(this).text());		
	});
	
	$('#btnPesq1').click(function(){
		buscaRegistro(1);
	});
	
	$('#bntPesq2').click(function(){
		buscaRegistro(1);
	});
	
	$("input[name=codigo]").keypress(function(e){
		if(e.wich == 13 || e.keyCode == 13){
			buscaRegistro(1);
		}
	});
	
	$('#addveiculo').click(function(){
		$('#adiveiculo').toggle('slow');
	});
	
	
	$('#codveicu').keypress(function(e){
		if(e.wich == 13 || e.keyCode == 13){
			
		}
		return false;
	});
	
	
});

function buscaRegistro(pagina){
	$("#resultado").show(500);
	$('#resultado').html('<div style="padding: 10px; border: 1px solid #d5d5d5;"><img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
	
	$.post('/admin/cadastro/buscaprodutos', {
		codigo			: $("input[name=codigo]").val(),
		internorol		: $("input[name=internorol]").val(),
		buscagrupo 		: $("select[name=buscagrupo] option:selected").val(),
		buscagruposub	: $("select[name=buscagruposub] option:selected").val(),
		pesqmedida 		: $("select[name=pesqmedida] option:selected").val(),
		internorol		: $("input[name=internorol]").val(),
		externorol		: $("input[name=externorol]").val(),
		alturarol		: $("input[name=alturarol]").val(),
		estm1			: $("input[name=estm1]").val(),
		estm2			: $("input[name=estm2]").val(),
		estf1			: $("input[name=estf1]").val(),
		estf2			: $("input[name=estf2]").val(),
		internoslin		: $("input[name=internoslin]").val(),
		externoslin		: $("input[name=externoslin]").val(),
		alturaslin		: $("input[name=alturaslin]").val(),
		dentesslin		: $("input[name=dentesslin]").val(),
		internocrus		: $("input[name=internocrus]").val(),
		externocrus		: $("input[name=externocrus]").val(),
		alturacrus		: $("input[name=alturacrus]").val(),
		page			: pagina
    },
    function(resposta) {	    	
    	$('#resultado').html(resposta);			       
    });	
}

function Mostramontakit(cod,qtt){

	$.ajax({
	  url: "/admin/cadastro/buscaprodutokit/q/"+cod+"/qt/"+qtt,
	  success: function(data) {
		  	var texto = data;
			texto = texto.replace(/\+/g," ");
			texto = unescape(texto);
			texto = texto.split("|");

			var arrId = document.getElementById('arrId').value;

			narrId = arrId.split(";");
			erro = 0;
			for(i=0;i < narrId.length; i++){
				if(texto[0]==narrId[i]){
					erro = 1;
				}
			}

			if(erro==1){
				/*alert('Este código já esta na lista');*/
				jAlert('Este código já esta na lista!', 'Erro!');
				document.getElementById("codbusca").focus();
			    return false;    
			}else if(texto[0]=="erro1"){
				/*alert('Código incorreto');*/
				jAlert('Código incorreto!', 'Erro!');
				document.getElementById("codbusca").focus();			
			}else{
				document.getElementById('arrId').value += texto[0]+";";
				
				var tbtable = document.getElementById("tbcomp");
				var numOfRows = tbtable.rows.length;
				var tbCod = tbtable.insertRow(numOfRows-2);
			
				var y= tbCod.insertCell(0);
				var z= tbCod.insertCell(1);
				var b= tbCod.insertCell(2);
				var c= tbCod.insertCell(3);
				var h= tbCod.insertCell(4);
				var d= tbCod.insertCell(5);
				var g= tbCod.insertCell(6);
				
				var sit = "";
				if(texto[7]==0){
					sit = "P";
				}else if(texto[7]==1){
					sit = "D";
				}else if(texto[7]==2){
					sit = "I";
				}
				
				y.align = "left";
				y.setAttribute("class","td_orc_min");
				y.innerHTML=texto[1]+"<input type='hidden' name='kit_"+texto[0]+"' value='"+qtt+"' >";
				z.align = "center";
				z.setAttribute("class","td_orc_min");
				z.innerHTML=sit;
				b.align = "left";
				b.setAttribute("class","td_orc_min");
				b.innerHTML=texto[8];
				c.align = "center";
				c.setAttribute("class","td_orc_min");
				c.innerHTML=qtt;
				h.align = "center";
				h.setAttribute("class","td_orc_min");
				h.innerHTML=texto[6];
				d.align = "right";
				d.setAttribute("class","td_orc_min");
				d.innerHTML=texto[5];
				g.align = "center";
				g.setAttribute("class","td_orc_min");
				g.innerHTML='<a href="javascript:void(0);" onclick="deleteRow(this.parentNode.parentNode.rowIndex,'+texto[0]+')"><img src="http://www.ztlbrasil.com.br/admin/images/window-close.png" width="13" heigth="13" border="0"></a>';
				
				document.getElementById("codbusca").focus();
				total = document.getElementById("totalkit").value;
				document.getElementById("idtotal").innerHTML = float2moeda(parseFloat(total)+parseFloat(texto[4]));
				document.getElementById("totalkit").value = parseFloat(total)+parseFloat(texto[4]);
			}
	  	}
	});
	
}

function buscaprod(){
	  var cod 	= document.getElementById('codbusca').value;
	  var qt 	= document.getElementById('qtbusca').value;
	  	  
	  if(cod==''){
	 	 /*alert('Digite o código do produto!');*/
		  jAlert('Digite o código do produto!', 'Erro!');
	 	 document.getElementById("codbusca").focus();
	  }else if(qt==''){
		 /*alert('Digite a quantidade!');*/
		  jAlert('Digite a quantidade!', 'Erro!');
	  }else{
		 Mostramontakit(cod,qt);
		  document.getElementById('codbusca').value = "";
	 	  document.getElementById('qtbusca').value = "";	 	  
	  }
}

function validaformproduto(){
	if(document.cad_prod.codigo.value==""){
		/*alert("O Código do produto não pode ficar em branco!");*/
		jAlert('O Código do produto não pode ficar em branco!', 'Erro!');
		document.cad_prod.codigo.focus();
		return false; 
	}else if(document.cad_prod.buscagrupo.value==0){
		/*alert("Selecione o grupo de venda do produto!");*/
		jAlert('Selecione o grupo de venda do produto!', 'Erro!');
		return false; 
	}else if(document.cad_prod.grupocompra.value==0){
		/*alert("Selecione o grupo de compra do produto!");*/
		jAlert('Selecione o grupo de compra do produto!', 'Erro!');
		return false; 
	}else if(document.cad_prod.fornecedor.value==0){
		/*alert("Selecione o fornecedor do produto!");*/
		jAlert('Selecione o fornecedor do produto!', 'Erro!');
		return false; 
	}else if(((document.cad_prod.precoajuste.value!="")||(document.cad_prod.ajusteperc.value!="")) && (document.cad_prod.dataajuste.value=="")){
		jAlert('Preencher a data do ajuste de preço!', 'Erro!');
		return false; 
	}else{
		return true;	
	}
}

function validaformprodutochina(){
	if(document.cad_prod.precoshunkang.checked){
		/*alert("O Código do produto não pode ficar em branco!");*/
		if(document.cad_prod.fornecedorkang.value=='0'){
			jAlert('Selecione o fornecedor Shukang!', 'Erro!');
			return false;
		}else{
			valin = 1;
		}
		
	} 
	
	if(document.cad_prod.precoshuntai.checked){
		/*alert("O Código do produto não pode ficar em branco!");*/
		if(document.cad_prod.fornecedortai.value=='0'){
			jAlert('Selecione o fornecedor Shuntai!', 'Erro!');
			return false;
		}else{
			valin = 1;
		}
		
	}else{
		return true;
	}
	
	if(valin==1){
		return true;
	}
	
	return false;
}



/*function promocao_preco(){
	document.getElementById('preco_desc').disabled=true;
	document.getElementById('preco_desc').value='';
	document.getElementById('preco_promo').disabled=false;
	document.cad_prod.preco_promo.focus();
}

function promocao_desc(){
	document.getElementById('preco_promo').disabled=true;
	document.getElementById('preco_promo').value='';
	document.getElementById('preco_desc').disabled=false;
	document.cad_prod.preco_desc.focus();
}*/

function ajute_preco(){
	document.getElementById('ajusteperc').disabled=true;
	document.getElementById('ajusteperc').value='';
	document.getElementById('precoajuste').disabled=false;
	document.cad_prod.precoajuste.focus();
}

function ajuste_perc(){
	document.getElementById('precoajuste').disabled=true;
	document.getElementById('precoajuste').value='';
	document.getElementById('ajusteperc').disabled=false;
	document.cad_prod.ajusteperc.focus();
}

function adcionaHistorico(){
	
	var forn  = document.getElementById('fornecedor_hist').value;
	var moeda = document.getElementById('moedahist').value;
	var prec  = document.getElementById('preco_hist').value;
	var data  = document.getElementById('data_hist').value;
	var balls = document.getElementById('balls').value;
	
	if(forn=="0"){
		jAlert('Selecione o fornecedor!', 'Erro!');
	}else if(prec==""){
		jAlert('O preço não pode ficar em branco!', 'Erro!');
	}else if(data==""){
		jAlert('A data não pode ficar em branco!', 'Erro!');
	}else{
		document.cad_prod.action="/admin/cadastro/gravahistcompra";
		document.cad_prod.submit();
	}
}

function buscaComposicaoprod(){
	
	$.ajax({
	  url: "/admin/cadastro/buscacomposicao/cod/"+cod,
	  success: function(data) {
		  if(data=="erro1"){
				jAlert('Código incorreto!', 'Erro!');
				$("#codcomp").focus();			
			}else{
				$('#idcomposicao').html(data);
			}
	  }
	});
	
}



function adcionaHistoricochina(){
	
	var forn  = document.getElementById('fornecedor_hist').value;
	var moeda = document.getElementById('moedahist').value;
	var prec  = document.getElementById('preco_hist').value;
	var data  = document.getElementById('data_hist').value;
	var balls = document.getElementById('balls').value;
	
	if(forn=="0"){
		jAlert('Selecione o fornecedor!', 'Erro!');
	}else if(prec==""){
		jAlert('O preço não pode ficar em branco!', 'Erro!');
	}else if(data==""){
		jAlert('A data não pode ficar em branco!', 'Erro!');
	}else{
		document.cad_prod.action="/admin/cadastro/gravahistcomprachina";
		document.cad_prod.submit();
	}
}



function buscaCustocompra(idncm,valor){
	$.ajax({
	  url: "/admin/compras/buscacustoporncm/idncm/"+idncm+"/valor/"+valor,
	  success: function(data) {
		  $("#indice").html(data);
	  }
	});
}
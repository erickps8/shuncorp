$(function(){

	var loader = '<div id="loader"><img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>';
	
	$("input[name=codigo]").keypress(function(e){
		
	});
	
	$('#addveiculo').click(function(){
		$('#adiveiculo').toggle('slow');
	});
	
	
	$('#codveicu').keypress(function(e){
		if(e.wich == 13 || e.keyCode == 13){
			
		}
		return false;
	});
	
	$('#btnAddveiculo').click(function(){
	
		var ano 		= 0;
		var idveic 		= $("select[name=veiculos] option:selected").val();
		var ano1 		= $('input[name=anoini]').val();
		var ano2 		= $('input[name=anofin]').val();
		var modelo 		= $('input[name=modelo]').val();
		var valvulas 	= $('input[name=valvulas]').val();
		var combustivel = $("select[name=combustivel] option:selected").val();
		var cambio 		= $('input[name=cambio]').val();
		var motor 		= $('input[name=motor]').val();
		var tracao 		= $("select[name=tracao] option:selected").val();
		
		var veic 		= parseInt($('input[name=contVeiculos]').val());
		veic++;
		
		if($("select[name=montadoras] option:selected").val() == 0){
			jAlert("Selecione uma montadora!", "Erro!");
			return false;			
		};
				
		var textcombustivel = (combustivel == '0') ? 'Todos' : $("select[name=combustivel] option:selected").text();
		
		ano1p = parseInt(ano1);
		ano2p = parseInt(ano2);
		
		if((ano1 == "") && (ano2 != "")){
			ano = "< "+ano2;
		}else if((ano2 == "") && (ano1 != "")){
			ano = ano1+" > ";
		}else if((ano1 == "") && (ano2 == "")){
			ano = "Todos";
		}else if(ano1p > ano2p){
			jAlert('Ano inicial não pode ser maior que Ano final!', 'Erro!');
			return false;    
		}else if(ano1p < ano2p){
			ano = ano1+" > "+ano2;
		}else if(ano1p == ano2p){
			ano = ano1;
		}
		
		var novaLinha = 
		'<tr>'+
			'<td align="left">'+$("select[name=montadoras] option:selected").text()+'</td>'+
			'<td align="left">'+$("select[name=veiculos] option:selected").text()+' '+tracao+' '+modelo+' '+motor+' '+valvulas+' '+cambio+
				'<input type="hidden" name="modelo_'+veic+'" value="'+modelo+'" >'+
				'<input type="hidden" name="motor_'+veic+'" value="'+motor+'" >'+
				'<input type="hidden" name="valvulas_'+veic+'" value="'+valvulas+'" >'+
				'<input type="hidden" name="tracao_'+veic+'" value="'+tracao+'" >'+
				'<input type="hidden" name="cambio_'+veic+'" value="'+cambio+'" >'+				
				'<input type="hidden" name="idveiculo_'+veic+'" value="'+idveic+'" >'+
			'</td>'+
			'<td align="center">'+ano+'<input type="hidden" name="anoini_'+veic+'" value="'+ano1+'" ><input type="hidden" name="anofin_'+veic+'" value="'+ano2+'" ></td>'+
			'<td align="center">'+textcombustivel+'<input type="hidden" name="combustivel_'+veic+'" value="'+combustivel+'" ></td>'+
			'<td align="center"><a href="javascript:void(0);" class="deleteRow"><img src="/public/sistema/imagens/window-close.png" width="13" ></a></td>'+
		'</tr>';
		
		$('#tbveiculos > tbody').append(novaLinha);
		
		$('input[name=contVeiculos]').val(veic);
		$('input[name=anoini]').val("");
		$('input[name=anofin]').val("");
		$('input[name=modelo]').val("");
		$('input[name=valvulas]').val("");
		$("select[name=combustivel]").val("");
		$('input[name=cambio]').val("");
		$('input[name=motor]').val("");
		$("select[name=tracao]").val(0);
		$("select[name=montadoras]").val(0);

		$("#diveiculos").html('<div class="styled-select" style="width: 150px"><select style="width: 172px"><option>Veículos</option></select></div>');
	});

	$('.deleteRow').live('click', function() {
		$(this).parent().parent().remove();
	});
	
	$('.deleteRowcomp').live('click', function() {
		$(this).parent().parent().remove();
		
		var id = $(this).attr('rel');
		var arrId = $('#arrId').val();
		
		$('#arrId').val(arrId.replace(";"+id+";",""));
		
	});
	
	function deleteRow(i,id){
	  document.getElementById('tbcomp').deleteRow(i);
	  var arrId = document.getElementById('arrId').value;
		document.getElementById('arrId').value = arrId.replace(id+";","");

	}
	
	$('select[name=montadoras]').change(function(){
		var id = $("select[name=montadoras] option:selected").val();
		
		$.ajax({
		  url: "/admin/cadastro/buscaveiculo/idmont/"+id,
		  success: function(data) {
			  $('#diveiculos').html(data);
		  }
		});
		
	});
	
	//-- Composicao ------------------------------------------------------------------------------------------------
	$('#addProd').click(function(){
		$('#adiprod').toggle();
		$('#codbusca').focus();
	});
	
	$('#addComp').click(function(){
		$('#copcomp').toggle();
		$('#codcomp').focus();
	});
	
	//-- Historico ------------------------------------------------------------------------------------------------
	$('#addHistorico').click(function(){
		$('#adihist').toggle('slow');
		$('#codbusca').focus();
	});
	
	
	//-- Copiar aplicacao ------------------------------------------------------------------------------------
	$('#copyAplicacao').click(function(){
		
		var cod  = $('input[name=codveicu]').val();
		if(cod==""){
			jAlert("Digite o código do produto a ser consultado!","Erro");
			return;
		}else{
			$.ajax({
			  url: "/admin/cadastro/buscacompveic/cod/"+cod,
			  success: function(data) {
			  	if(data=="erro1"){
					jAlert('Código incorreto!', 'Erro!');
					$('#codveicu').focus();			
			  	}else if(data=="erro2"){
					jAlert('Produto não possue aplicação!', 'Erro!');
					$('#codveicu').focus();			
				}else{
					$('#tbodyAplicacao').html(data);
					
					$.ajax({
						url: "/admin/cadastro/buscacompveic/tp/1/cod/"+cod,
						success: function(data) {
							$('input[name=contVeiculos]').val(data);
						}
					});					
				}
			  }
			});
		}
		
		
		$('#atualizaProd').click(function(){
			jConfirm('Você deseja atualizar este produto? Este procedimento não poderá ser desfeito.', 'Confirme', function(r) {
				if(r==true){
					
					var loader = '<div style="padding: 10px; border: 1px solid #d5d5d5;"><img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>';
					jModal(loader, "Atualizando", 200, 100);
					
					var prod = $('input[name=codigoprod]').val();
					
					$.ajax({
					  url: '/admin/soap/atualizaproduto/codigo/'+prod,
					  success: function(data) {
						  
						  console.log(data);
						  
						  data = data.split("|");
						  jAlert(data[2],data[1],function(){
							  window.location = window.location; 
						  });
						  
					  }
					});
				}
			});
		});
		
	});
		
	//-- Composicao --------------------------------
	$("input[name='qtbusca']").blur(function(){
		  	  
		var cod = $("input[name='codbusca']").val(); 
		var qtt = $("input[name='qtbusca']").val();
		
		if(cod==''){
			jAlert('Digite o código do produto!', 'Erro!');
			$("input[name='codbusca']").focus();
		}else if(qtt==''){
			jAlert('Digite a quantidade!', 'Erro!');
		}else{
			 
			$.ajax({
				url: "/admin/cadastro/buscaprodutokit/q/"+cod+"/qt/"+qtt,
				success: function(data) {
					data = unescape(data.replace(/\+/g," "));
					var texto = data.split("|");
	
					var arrId = $('#arrId').val();
	
					narrId = arrId.split(";");
					erro = 0;
					for(i=0;i < narrId.length; i++){
						if(texto[0]==narrId[i]){
							erro = 1;
						}
					}
	
					if(erro==1){
						jAlert('Este código já esta na lista!', 'Erro!');
						$("input[name='codbusca']").focus();
					    return false;    
					}else if(texto[0]=="erro1"){
						jAlert('Código incorreto!', 'Erro!');
						$("input[name='codbusca']").focus();			
					}else{
						$('#arrId').val($('#arrId').val()+";"+texto[0]+";");
						
						var sit = "";
						if(texto[7]==0) sit = "P";
						else if(texto[7]==1) sit = "D";
						else if(texto[7]==2) sit = "I";
						
						var novaLinha = 
						'<tr>'+
							'<td align="left">'+texto[1]+'<input type="hidden" name="kit_'+texto[0]+'" value="'+qtt+'" ></td>'+
							'<td align="center">'+sit+'</td>'+
							'<td align="center">'+texto[8]+'</td>'+
							'<td align="center">'+qtt+'</td>'+
							'<td align="center">'+texto[6]+'</td>'+
							'<td align="center">'+texto[5]+'</td>'+
							'<td align="center"><a href="javascript:void(0);" class="deleteRowcomp" rel="'+texto[0]+'"><img src="/public/sistema/imagens/window-close.png" width="13" ></a></td>'+
						'</tr>';
						
						$('#tbcomp > tbody').append(novaLinha);
						
						$("input[name='codbusca']").focus();
						total = $("#totalkit").val();
						$("#idtotal").html(float2moeda(parseFloat(total)+parseFloat(texto[4])));
						$("#totalkit").val(parseFloat(total)+parseFloat(texto[4]));
					}
			  	}
			});
			 
			$("input[name='codbusca']").val("");
			$("input[name='qtbusca']").val("");	 	  
		}
	});
		
	//-- Copia composicao --------------------------------
	$("input[name='buscacomp']").click(function(){
		
		var cod  = $('input[name=codcomp]').val();
		if(cod==""){
			jAlert("Digite o código do produto a ser copiado!","Erro");
			return;
		}else{
			$.ajax({
				url: "/admin/cadastro/buscacomposicao/cod/"+cod,
				success: function(data) {
					if(data=="erro1"){
						jAlert('Código incorreto!', 'Erro!');
						$("#codcomp").focus();						
					}else{
						$('#tbodyComposicao').html(data);
						
						$.ajax({
							url: "/admin/cadastro/buscacomposicao/cod/"+cod+"/tp/1",
							success: function(datasoma) {
								var somacomp = datasoma.split("|");
								
								$('#arrId').val(somacomp[0]);
								$('#totalkit').val(somacomp[1]);
								$('#idtotal').html(somacomp[2]);								
							}
						});	
						
					}
				}
			});	
		}
	});
	
	//-- anexos ------------------------------
	var intarc = 1;
	$("#fileInput").live('change', function() {
		
		$("#qtarquivo").val(intarc);
		
    	intarc = intarc + 1;
		//var tbCod = document.getElementById("archive").insertRow(-1);
		//var y = tbCod.insertCell(0);
		
    	novaLinha = '<div class="fix"></div><div style="width: 100%"><div style="float: left; width: 180px">Descrição:<br /><input type="text" name="nomearq_'+intarc+'" style="width: 150px"></div>'+
    				'<div style="float: left; width: 450px">Arquivo:<br />'+
    				'<input id="arquivo_'+intarc+'" style="display: inline; width: 300px;" class="file fileInput"><div style="display: inline; position: absolute; overflow: hidden;" class="feat"><input style="position: relative; height: 26px; width: 300px; display: inline; cursor: pointer; opacity: 0; margin-left: -168px;" class="fileInput" id="fileInput" name="arquivo_'+intarc+'" type="file"></div>'+
    				'</div></div>';
    	
		$('#divArquivos').append(novaLinha);
				        		
		
	});
	
	//--historico fornecedor --------------------------------------
	$("#btnAddhistoricofor").click(function(){
		var forn  = $("select[name=fornecedor_hist] option:selected").val();
		var moeda = $("select[name=moedahist] option:selected").val();
		var prec  = $('input[name=preco_hist]').val();
		var data  = $('input[name=data_hist]').val();
		var balls = $('input[name=balls]').val();
		var prod  = $('input[name=id_produto]').val();
		
		forn = forn.split("-");
		
		if(forn[0]=="0"){
			jAlert('Selecione o fornecedor!', 'Erro!');
		}else if(prec==""){
			jAlert('O preço não pode ficar em branco!', 'Erro!');
		}else if(data==""){
			jAlert('A data não pode ficar em branco!', 'Erro!');
		}else{
			//document.cad_prod.action="/admin/cadastro/gravahistcompra";
			//document.cad_prod.submit();
			
			$.post('/admin/cadastro/gravahistcomprachina', {
				id_produtos: 		prod,
				preco_hist: 		prec, 
				balls: 				balls,
				data_hist:			data,
	    		fornecedor_hist:	forn[0],
	    		moedahist:			moeda	    		
    		},
		    function(resposta) {
		    	if(resposta == true){
		    		
		    		novaLinha = '<tr><td style="text-align: center;">'+data+
		    					'<td align="left">'+forn[1]+'</td>'+
		    					'<td style="text-align: center;">'+moeda+'</td>'+
		    					'<td style="text-align: right;">'+prec+'</td>'+
		    					'<td style="text-align: center;">'+balls+'</td>'+
		    					'<td style="text-align: center;">'+
		    						'<a href="javascript:void(0);" class="removeHistorico" rel="'+'"><img src="/public/sistema/imagens/icons/middlenav/close.png" width="13" border="0"></a>'+
		    					'</td></tr>';
    	
		    		$('#boryHistfornec').append(novaLinha);
		    		
		    	}else{
		    		jAlert('Erro ao gravar o histórico! Tente novamente.','Erro!');
		    	}
		    });
			
		}
	});
	
	//-- remove anexo --------------------------------------------------
	$(".removeAnexo").click(function(){
		var anexo = $(this);
				
		jConfirm('Você deseja remover este anexo?', 'Confirme', function(r) {
			if(r==true){
				$.ajax({
				  url: "/admin/cadastro/removearquivos/idarq/"+anexo.attr('rel'),
				  success: function(data) {
					  anexo.parent().parent().remove();
				  }
				});				
			}
		});
	});
	
	//-- remove imagens --------------------------------------------------
	$(".removeImagens").click(function(){
		var anexo = $(this);
				
		jConfirm('Você deseja remover a imagem '+anexo.attr('rel')+'?', 'Confirme', function(r) {
			if(r==true){
				$.ajax({
				  url: "/admin/cadastro/removeimagen/produto/"+$("input[name=id_produto]").val()+"/img/"+anexo.attr('rel'),
				  success: function(data) {
					  jAlert("Imagem removida com sucesso!", "Sucesso!", function(){
						  window.location = window.location; 
					  });
				  }
				});				
			}
		});
	});
	
	//-- remove historico compra --------------------------------------------------
	
	$(".removeHistorico").live('click', function(){
		var anexo = $(this);
		
		jConfirm('Você deseja remover o registro deste histórico?', 'Confirme', function(r) {
			if(r==true){
				$.ajax({
				  url: "/admin/cadastro/removehistoricochina/idihistc/"+anexo.attr('rel'),
				  success: function(data) {
					  jAlert("Histórico removido com sucesso!", "Sucesso!");
					  
					  anexo.parent().parent().remove();
				  },
				  error: function(data){
					  jAlert("Erro ao remover histórico de compra! Tente novamente.", "Erro!");
				  }
				});				
			}
		});
	});
	
	$(".removeHistorico").click(function(){
		var anexo = $(this);
				
		jConfirm('Você deseja remover o registro deste histórico?', 'Confirme', function(r) {
			if(r==true){
				$.ajax({
				  url: "/admin/cadastro/removehistoricochina/idihistc/"+anexo.attr('rel'),
				  success: function(data) {
					  jAlert("Histórico removido com sucesso!", "Sucesso!");
					  
					  anexo.parent().parent().remove();
				  },
				  error: function(data){
					  jAlert("Erro ao remover histórico de compra! Tente novamente.", "Erro!");
				  }
				});				
			}
		});
	});
	
	//-- gravar produto -------------------------------------------
	$("#btnSalvar").click(function(){
		
		$('input, textarea, button, select').attr('disabled',false);
		
		var subgrupo = $('select[name=buscagruposub] option:selected').val();
		var grupo = $('select[name=buscagrupo] option:selected').val();
		
		if($('input[name=codigo]').val() == ""){
			jAlert("O Código não pode ficar em branco!", "Erro!");
		}else if(subgrupo == 0){
			jAlert("O Subgrupo de venda não pode ficar em branco!", "Erro!");
		}else if(grupo == 0){
			jAlert("O grupo de venda não pode ficar em branco!", "Erro!");
		}else{
		
			$('form[name=cadProduto]').ajaxForm({
				url: "/admin/cadastro/cadproduto",
				beforeSubmit:  function(){
					jLoader("Aguarde", "Salvando....", 600, 150);		
				}, 
		    	success: function(data) {
		    		console.log(data);
		    		
		    		data = data.split("|");
		    		
		    		if(data[0] == "erro"){
		    			jAlert(
			    			"Erro ao salvar o produto! Tente novamente. <img style='cursor: pointer' id='btnDet' title='' src='/public/sistema/imagens/icons/dark/alert2.png' >"+
			    			"<div style='display: none' id='divDet'>"+data[1]+"</div>"
			    			, "Erro!");
		    			
		    		}else if(data[0] == "0"){
		    			jAlert(
		    				"Produto salvo com sucesso, porém alguns campos não foram salvos! <img style='cursor: pointer' id='btnDet' title='' src='/public/sistema/imagens/icons/dark/alert2.png' >"+
		    				"<div style='display: none' id='divDet'>"+data[1]+"</div>"
		    				, "Erro!"); 		    			
		    		}else if(data[0] == "1"){
		    			jAlert("Produto salvo com sucesso!", "Sucesso!", function(){
		    				window.location = window.location;
		    			});
		    		}		    		
	            },  
	            error: function() {  
	            	jAlert("Erro ao salvar o produto! Tente novamente.", "Erro!");  
	            } 
		    }).submit();
		}
	});
	
	
	$("#btnDet").live('click', function(){
		$("#divDet").toggle();
	});
	
	$("#imgProd1").click(function(){
		var idprod = $("input[name=id_produto]").val();
		jAlert(
			"<img width='400' src='/public/sistema/upload/produtos/imagens/"+idprod+"/imagem1.jpg' >"
			, "Imagem do produto"
		);			
	});
	
	$("#imgProd2").click(function(){
		var idprod = $("input[name=id_produto]").val();
		jAlert(
			"<img width='400' src='/public/sistema/upload/produtos/imagens/"+idprod+"/imagem1_"+idprod+".jpg' >"
			, "Imagem do produto"
		);			
	});
	
	
	
	$("#fornecedorkang").change(function(){
		
		var idprod 	= $('input[name=id_produto]').val();
		var forn	= $(this).val(); 
		
		$.ajax({
			  url: "/admin/cadastro/buscacrossporfornecedor/idprod/"+idprod+"/forn/"+forn,
			  success: function(data) {
				  	
				  data = data.replace(/\+/g," ");
				  data = unescape(data);
					
				  $('#codigofornecedor').html(data);
			  },
			  error: function(data){
				  jAlert("Erro ao buscar produto no cross reference! Tente novamente.", "Erro!");
			  }
		});	
	});
	
	$("#fornecedortai").change(function(){
		
		var idprod 	= $('input[name=id_produto]').val();
		var forn	= $(this).val(); 
		
		$.ajax({
			  url: "/admin/cadastro/buscacrossporfornecedor/idprod/"+idprod+"/forn/"+forn,
			  success: function(data) {
				  	
				  data = data.replace(/\+/g," ");
				  data = unescape(data);
					
				  $('#codigofornecedortai').html(data);
			  },
			  error: function(data){
				  jAlert("Erro ao buscar produto no cross reference! Tente novamente.", "Erro!");
			  }
		});	
	});

	//-- Copiar aplicacao ------------------------------------------------------------------------------------
	$('#btnAnexos').click(function(){
		var prod = $('input[name=codigoprod]').val();

		$('#containerAnexos').html('<div style="border: 1px solid #d5d5d5; padding: 20px"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, buscando anexos...</i></div>');

		$.ajax({
			url: "/admin/cadastro/getanexos/codigo/"+prod,
			success: function(data) {
				$('#containerAnexos').html(data);
			},
			error: function () {
				$('#containerAnexos').html("Ocorreu um erro tentar conectar no servidor.");
			}
		});
	});
	
});

function bloqCampos(){
	$('input, textarea, button, select').attr('disabled','disabled');
	$('.desbloqueio').attr('disabled', false);
}
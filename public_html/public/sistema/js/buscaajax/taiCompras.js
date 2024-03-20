$(document).ready(function(){
		
	//-- Pedidos de compra ------------------------------------------------------
	var pagina = 1;
	
	//-- Ppaginacao ------------------------------------------------
	$('.btnPaginator').live('click', function() {
		pagina = $(this).text();		
		$('#btnbusca').trigger('click');
	});
	
	$('#btnbusca').click(function(){
		
		$("#resultado").show(500);
		$('#resultado').html('<div style="padding: 10px; border: 1px solid #d5d5d5;">'+
		'<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
		
		$.post('/admin/shuntaicompras/buscapedidoscompra', {
			buscaid			: $("input[name=buscaid]").val(),
			order			: $("input[name=order]").val(),
			buscasit 		: $("select[name=buscasit] option:selected").val(),
			buscacli 		: $("select[name=buscacli] option:selected").val(),
			dataini			: $("input[name=dataini]").val(),
			datafin			: $("input[name=datafin]").val(),
			codigo			: $("input[name=codigo]").val(),
			page			: pagina
	    },
	    function(resposta) {
	    	$('#resultado').html(resposta);			       
	    });	
	});
	
	$('#btnbusca').trigger('click');
	
	$('.removeCompra').live('click', function(){
		var compra = $(this).attr('rel');
		
		jConfirm('Você deseja remover esta compra?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/shuntaicompras/pedidosrem/rem/'+compra;				
			}
		});
		
	});
	
	$('.finalizaFinanceiro').live('click', function(){
		var compra = $(this).attr('rel');
		
		jConfirm('Finalizar o financeiro desta compra?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/shuntaicompras/pedidosfin/fin/'+compra;
			}
		});
	});
	
	$('#fecharPedido').click(function(){
		jConfirm('Finalizar esta compra?', 'Confirme', function(r) {
			if(r==true){
				$('form[name=prepedido]').attr('action', '/admin/shuntaicompras/fecharcompra');
				$('form[name=prepedido]').submit();				
			}
		});
	});
	
	$('.removeEntrega').click(function(){
		var ped = $(this).attr('rel');
		ped = ped.split('|');
				
		jConfirm('Remover esta entrega?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/shuntaicompras/removeentrega/rem/'+ped[1]+'/ped/'+ped[0];
			}
		});
	});
	
	//-- regras de compras ---------------------------------------------
	$('.regrasCompra').click(function(){
		$.ajax({
			url: "/admin/shuntaicompras/buscaregrascomprasgrupo/compra/"+$(this).attr('rel'),
			success: function(data) {
				jModal(data, "Regras de compra", 800, 200);
			}
		});		
	});
	
	$('#contasval').live('change', function(){
		var grupo = $("#contasval option:selected").val();
		var compra = $("#compra").val();
		
		$.ajax({
			url: "/admin/shuntaicompras/buscaregrascompras/grupo/"+grupo+"/compra/"+compra,
			success: function(data) {
				$('#respostaRegras').html(data);
			}
		});		
	});
	
	$('.checkRegra').live('click', function(){
		
		var regras = $('#idregras').val();
		$('#idregras').val(regras.replace($(this).val()+";", ""));
		
		if($(this).is(":checked") == true){
			$('#idregras').val($('#idregras').val()+$(this).val()+";");
		}
				
	});
	
	$('#salvarRegras').live('click', function(){
		
		$.post('/admin/shuntaicompras/gravarobs', {
			idregras	: $("input[name=idregras]").val(),
			compra		: $("input[name=compra]").val()
	    }, 
	    function(resposta) {
	    	if(resposta == true){
	    		jAlert("Regras registradas com sucesso!", "Sucesso!");
	    	}else{
	    		jAlert("Erro ao registrar regras! Tente novamente.", "Erro!");
	    	}
	    });		
	});
	
	
	$('#gerarCompra').change(function(){
		
		if($("#fornecedor option:selected").val() == 0){
			jAlert('Selecione um fornecedor!','Erro!');
		}else{			
		    if($("#gerarCompra option:selected").val() == 1){
		    	$('form[name=prepedido]').attr('action', '/admin/shuntaicompras/gerarpedidoman');
				$('form[name=prepedido]').submit();
		    }else if($("#gerarCompra option:selected").val() == 2){
		    	$('form[name=prepedido]').attr('action', '/admin/shuntaicompras/listaprodutos');
				$('form[name=prepedido]').submit();
		    }
		}
	});
	
	$('#validaCompra').click(function(){
		var erro = 0;			
		form = document.prepedido;
		for (i=0;i<form.length;i++){
			var obg = form[i].id;
			if(form[i].type=="text"){
				if(document.getElementById(form[i].id).value!=""){
					form.submit();
					erro = 1;
				}
			}
		}
		if(erro==0){
			jAlert("Nenhum produto selecionado!","Erro!");
		}
	});
	
	$('#validaCompraprod').click(function(){
		if($("#arrId").val() !=""){
			document.prepedido.submit();
		}else{
			jAlert('Nenhum produto inserido!', 'Erro!');
		}
	});
	
	
	$('#qt').blur(function(){
		
		var prod 		= $('#codigo').val();
		var qt	 		= $('#qt').val();
		var fornecedor 	= $('input[name=fornecedor]').val();
		
		$.ajax({
			url: "/admin/shuntaicompras/buscaproduto/q/"+prod+"/fornecedor/"+fornecedor+"/qt/"+qt,
			success: function(data) {
				
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
					$('#resultado').html('Este produto já está na lista');
				    return false;				    
				}else if(texto[0]=="erro1"){
					$('#resultado').html('Código incorreto');
					$("#codigo").focus();
				}else if(texto[4]!=fornecedor){
					$('#resultado').html('Este produto não é deste fornecedor');
					$("#codigo").focus();
				}else{

					$('#arrId').val($('#arrId').val()+texto[0]+";");
										
					var novaLinha = 
					'<tr>'+
						'<td align="center">'+texto[1]+'</td>'+
						'<td align="center">'+qt+'<input type="hidden" name="'+texto[0]+'" value="'+qt+'" ></td>'+
						'<td align="left">'+texto[3]+'</td>'+
						'<td align="right">'+texto[5]+'</td>'+
						'<td align="right">'+texto[6]+'</td>'+
						'<td align="center"><a href="javascript:void(0);" class="deleteRow" rel="'+texto[0]+'"><img src="/public/sistema/imagens/window-close.png" width="13" ></a></td>'+
					'</tr>';
					
					$('#tabela > tbody > tr:last').before(novaLinha);
					
					$("#codigo").val("");
					$("#qt").val("");
					$("#codigo").focus();
				
				}
			}
		});	
	});
	
	$('.deleteRow').live('click', function() {
		
		alert($(this).attr('rel'));
		
		$('#arrId').val($('#arrId').val().replace($(this).attr('rel')+";",""));
		$(this).parent().parent().remove();
	});

	
});
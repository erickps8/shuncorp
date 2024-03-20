$(document).ready(function(){
	$('.editarGrupo').click(function(){
		var grupo = $(this).attr('rel').split('|');
		
		$('input[name=id]').val(grupo[0]);
		$('input[name=grupo]').val(grupo[1]);
		
	});
	
	$('.exibeRegras').click(function(){
		var grupo = $(this).attr('rel');
		window.location = "/admin/kang/regracompras/grupo/"+grupo;		
	});
	
	$('.removeGrupo').click(function(){
		
		var grupo = $(this).attr('rel');
		
		jConfirm('Você deseja remover este grupo e todas suas regras?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/kang/removeregracomprasgrupo/grupo/'+grupo;
			}
		});
	});
	
	$('.editarRegra').click(function(){
		var idregra = $(this).attr('rel');
		
		$('input[name=id]').val(idregra);
		$('input[name=ingles]').val($('input[name=ingles_'+idregra+']').val());
		$('input[name=chines]').val($('input[name=chines_'+idregra+']').val());
	});
	
	$('.removeRegra').click(function(){
		
		var regra = $(this).attr('rel');
		
		jConfirm('Você deseja remover essa regra?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/kang/removeregracompra/idregra/'+regra;
			}
		});
	});
	
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
		
		$.post('/admin/kang/buscapedidoscompra', {
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
		var compra = $("#pedido").val();
		
		jConfirm('Você deseja remover este pedido de compra?', 'Confirme', function(r) {
			if(r==true){
				window.location = '/admin/kang/removecompra/ped/'+compra;				
			}
		});
		
	});
	
	$('.finalizaFinanceiro').live('click', function(){
		var compra = $(this).attr('rel');
		
		jConfirm('Finalizar o financeiro desta compra?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/kang/baixarfinanceirocompra/ped/'+compra; 
			}
		});
	});
	
	$('#fecharPedido').click(function(){
		jConfirm('Finalizar esta compra?', 'Confirme', function(r) {
			if(r==true){
				$('form[name=prepedido]').attr('action', '/admin/kang/fecharcompra');
				$('form[name=prepedido]').submit();
			}
		});
	});
	
	$('.removeEntrega').click(function(){
		var ped = $(this).attr('rel');
		ped = ped.split('|');
				
		var linha = $(this);
		
		jConfirm('Remover esta entrega?', 'Confirme', function(r) {
			if(r==true){
				$.ajax({
					url: '/admin/kang/removeentrega/rem/'+ped[1]+'/ped/'+ped[0],
					success: function(data) {
						linha.parent().parent().remove();
					}
				});
			}
		});
	});
	
	//-- regras de compras ---------------------------------------------
	$('.regrasCompra').click(function(){
		$.ajax({
			url: "/admin/kang/buscaregrascomprasgrupo/compra/"+$(this).attr('rel'),
			success: function(data) {
				jModal(data, "Regras de compra", 800, 200);
			}
		});		
	});
	
	$('#contasval').live('change', function(){
		var grupo = $("#contasval option:selected").val();
		var compra = $("#compra").val();
		
		$.ajax({
			url: "/admin/kang/buscaregrascompras/grupo/"+grupo+"/compra/"+compra,
			success: function(data) {
				$('#respostaRegras').html(data);
			}
		});		
	});
	
	
	$('.checkRegra').live('click', function(){
		
		var regras = $('#idregras').val();
		$('#idregras').val(regras.replace(";"+$(this).val()+";", ""));
		
		if($(this).is(":checked") == true){
			$('#idregras').val($('#idregras').val()+";"+$(this).val()+";");
		}
				
	});
	
	$('#salvarRegras').live('click', function(){
		
		$.post('/admin/kang/gravarobs', {
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
	
	$(".up-qcr").click(function(){
    	
		var id = $(this).attr('rel');
		
    	var data = '<form name="upqcr" enctype="multipart/form-data"  method="post"><div class="rowElem" style="text-align: left"><div class="formRight">'+
        '<input id="arquivoqcr" style="display: inline; width: 150px;" class="file fileInput">'+
        '<div style="display: inline; position: absolute; overflow: hidden;" class="feat">'+
        '<input style="position: relative; height: 26px; width: 150px; display: inline; cursor: pointer; opacity: 0; margin-left: -58px;" class="fileInput" id="fileInput" name="arquivoqcr" type="file"></div>'+
    	'</div><div class="fix"></div></div> <input type="hidden" name="idkangprod" value="'+id+'"> </form>';
    	
    	jModal(data, "Anexo", 350, 400);
    });
	
	$("input[name=arquivoqcr]").live('change',function(){
		//$('form[name=upqcr]').submit();
		
		$('form[name=upqcr]').ajaxForm({
			url: "/admin/kang/uploadqcr",
			type: 'post',
			beforeSubmit:  function(){
				jLoader("Aguarde", "Enviando....");		
			}, 
			success: function(data) {
				
	    		if(data == 1){
	    			window.location = window.location;
	    		}else{
	    			jAlert("Erro ao salvar o arquivo! Tente novamente.", "Erro!");  
	    		}
            },  
            error: function() {  
            	jAlert("Erro ao salvar o arquivo! Tente novamente.", "Erro!");  
            } 
	    }).submit();
	});
	
	$("#btnEditar").click(function(){
		window.location = window.location + "/editar/1"; 
	});
	
	$("#btnSalvar").click(function(){
				
		$('form[name=prepedido]').ajaxForm({
			url: "/admin/kang/editarpedidoscompra",
			type: 'post',
			beforeSubmit:  function(){
				jLoader("Aguarde", "Salvando....");
			}, 
			success: function(data) {		
				
				data = data.replace(/^\s+|\s+$/g,"");
				
	    		if(data == 'erro'){
	    			jAlert("Erro ao salvar os registros! Tente novamente.", "Erro!");
	    		}else{
	    			jAlert("Registros salvos com sucesso!", "Sucesso!", function(){
	    				window.location = "/admin/kang/pedidoscompraped/ped/"+data;
	    			});	    			  
	    		}
            },  
            error: function() {  
            	jAlert("Erro ao salvar os registros! Tente novamente.", "Erro!");  
            } 
	    }).submit();
	});
	
});
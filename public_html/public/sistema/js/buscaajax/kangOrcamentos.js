$(document).ready(function(){

	$('input[name=codigo]').focus();
	
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
		
		$.post('/admin/kang/buscapedidos', {
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
	
	$('#codigo').blur(function(){
		$('#resultado').html("");
		
		$.ajax({
			url: "/admin/kang/buscaproduto/q/"+$('#codigo').val(),
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
						erro = 3;
					}
				}
				
				if(erro==3){
					$('#resultado').html('Este produto já está na lista!');
					$('#codigo').val("");
				    return false;		    
				}else if(texto[0]=="erro1"){
					$('#resultado').html('Codigo incorreto!');
					$('#codigo').val("");
				}else if(texto[0]=="erro4"){
					$('#resultado').html('Produto não disponível para venda!');
					$('#codigo').val("");
				}else{
					$('#arrId').val($('#arrId').val()+texto[0]+";");
					$('#qtprod').val(texto[3]);
				}
			}
		});
		
	});
	
	$('#preco').blur(function(){
		if(document.prepedido.codigo.value==""){
			jAlert("Digite um código de produto!","Erro!");
		}else if(document.prepedido.qt.value==""){
			jAlert("Digite a quantidade do produto!","Erro!");
		}else if(document.prepedido.preco.value==""){
			jAlert("Digite o valor do produto!","Erro!");
		}else{
			document.prepedido.submit();
		}
	});

	
	$('.removeCompra').click(function(){
		
		var compra = $(this).attr('rel');
		
		jConfirm('Você deseja cancelar este pedido?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/kang/removecompra/ped/'+compra+'/tp/2/purc/'+$('input[name=pedido]').val();
			}
		});
	});
	
	
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
	
	
	$('#fecharPedido').click(function(){
		jConfirm('Finalizar todas as compras?', 'Confirme', function(r) {
			if(r==true){
				$('input[name=fecharpedido]').val(1);
				$('form[name=prepedido]').submit();
			}
		});
	});
	
	$('.removeEntrega').click(function(){
		
		var ped = $(this).attr('rel');
		ped = ped.split('|');
				
		jConfirm('Revomer esta entrega?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/kang/removeentrega/rem/'+ped[1]+'/tp/2/ped/'+ped[0];
			}
		});
	});
	
	$(".up-qcr").click(function(){
    	
		var id = $(this).attr('rel');
		
		$("input[name=formqcr]").val("upqcr"+id);
		
    	var data = '<form name="upqcr'+id+'" enctype="multipart/form-data"  method="post"><div class="rowElem" style="text-align: left"><div class="formRight">'+
        '<input id="arquivoqcr" style="display: inline; width: 150px;" class="file fileInput">'+
        '<div style="display: inline; position: absolute; overflow: hidden;" class="feat">'+
        '<input style="position: relative; height: 26px; width: 150px; display: inline; cursor: pointer; opacity: 0; margin-left: -58px;" class="fileInput" id="fileInput" name="arquivoqcr" type="file"></div>'+
    	'</div><div class="fix"></div></div> <input type="hidden" name="idkangprod" value="'+id+'"> </form>';
    	
    	jModal(data, "Anexo", 350, 400);
    });
	
	$("input[name=arquivoqcr]").live('change',function(){
		
		var form = $("input[name=formqcr]").val();
		
		console.log(form);
		
		
		
		$("form[name='"+form+"']").ajaxForm({
			url: "/admin/kang/uploadqcr",
			type: 'post',
			beforeSubmit:  function(){
				jLoader("Aguarde", "Enviando....");		
			}, 
			success: function(data) {
				
				console.log(data);
				
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
	
});
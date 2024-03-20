$(function(){  	
	
	buscarRegistros(1);
	
	$('input[name=veiculo]').keyup(function(e) {
		if (e.keyCode == '13') {
			buscarRegistros(1);
			return false;
		}
		
		if($('input[name=veiculo]').val().length >= 3){
			buscarRegistros(1);
		} 
		
	});
	
	//-- Paginacao ------------------------------------------------
	$('.btnPaginator').live('click', function() {
		var rel = $(this).attr('rel');
		if(rel=='ant'){
			rel = parseInt($('#pag').val())-1;
		}else if(rel=='pos'){
			rel = parseInt($('#pag').val())+1;
		}else{
			rel = $(this).text();
		}
		buscarRegistros(rel);
	});
	
	$('#btnbusca').click(function(){
		buscarRegistros(1);
	});
	
	$('#orderId').live('click', function(){
		
		var col = ($("input[name='ordem']").val() == 2) ? 1 : 2;
		$("input[name='ordem']").val(col);
		$("input[name='coluna']").val("1");
		
		buscarRegistros($('#pag').val());
	});

	$('#orderVeic').live('click', function(){
		var col = ($("input[name='ordem']").val() == 2) ? 1 : 2;
		$("input[name='ordem']").val(col);
		$("input[name='coluna']").val("2");
		
		buscarRegistros($('#pag').val());
	});
	
	
	function buscarRegistros(pag){	
		
		var col = $("input[name='coluna']").val();
		var ord = $("input[name='ordem']").val();
						
		$('#resultado').html('<div style="border: 1px solid #d5d5d5; padding: 20px"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, buscando veículos...</i></div>');
				
	    $.post("/admin/cadastro/buscaveiculos", {
	    	veiculo: 		$("input[name='veiculo']").val(),
	    	montadora: 		$("input[name='montadora']").val(),
	    	ordem: 			ord,
	    	coluna: 		col,
	    	page:			pag	    	
	    },
	    function(resposta) {
	    	if(resposta == 'erro'){
	    		jAlert('Erro ao buscar as veiculos!','Erro!');
	    		$('#resultado').html("");
	    	}else{
	    		$('#resultado').html(resposta);
	    	}
	    });	
	}
	
	$('#removeCadasdro').live('click', function(){
		
		var mont = $(this).attr('rel');
		
		jConfirm('Deseja remover este cadastro?', 'Confirme', function(r){
			if(r==true){
				
				$.post("/admin/cadastro/removeveiculos", {veiculo: mont},
			    function(resposta) {
			    	if(resposta == 'erro'){
			    		jAlert('Erro ao excluir cadastro! Tente novamente.','Erro!');	    		
			    	}else{
			    		jAlert('Cadastro excluido com sucesso!','Sucesso!');
			    		pag = parseInt($('#pag').val());
			    		buscarRegistros(pag);
			    	}
			    });
			}
		});		
			
	});
	
});
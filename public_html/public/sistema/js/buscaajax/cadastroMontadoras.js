$(function(){  	
	
	buscarRegistros(1);
	
	$('input[name=montadora]').keyup(function(e) {
		if (e.keyCode == '13') {
			buscarRegistros(1);
			return false;
		}
		
		if($('input[name=montadora]').val().length >= 3){
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
		var order = $(this).attr('rel');  
		buscarRegistros(1,order,1);
	});

	$('#orderMont').live('click', function(){
		var order = $(this).attr('rel');  
		buscarRegistros(1,order,2);
	});
	
	
	function buscarRegistros(pag, ord, col){	
		
		$('#resultado').html('<div style="border: 1px solid #d5d5d5; padding: 20px"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, buscando montadoras...</i></div>');
				
	    $.post("/admin/cadastro/buscamontadoras", {
	    	montadora: 		$("input[name='montadora']").val(),
	    	ordem: 			ord,
	    	coluna: 		col,
	    	page:			pag	    	
	    },
	    function(resposta) {
	    	if(resposta == 'erro'){
	    		jAlert('Erro ao buscar as montadoras+!','Erro!');
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
				
				$.post("/admin/cadastro/removemontadoras", {montadora: mont},
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
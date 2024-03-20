$(function(){   
	
    $("#btnbusca").click(function(){
		
		$("#resultado").show();
		$("#resultado").html('<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i>');
		
		//var data = $('#funcionarios').serialize();
		
		$.post('/admin/venda/buscapendbaixadas', {
			empresa			: $("input[name=empresa]").val(),
			dataini			: $("input[name=dataini]").val(),
	    	datafim			: $("input[name=datafim]").val(),
	    	canceladas		: $("input[name=canceladas]:checked").val()
	    },
	    function(resposta) {
			if(resposta == false){	
				jAlert('Erro ao buscar pendências! Tente novamente.', 'Erro!');
			}else{
				$("#resultado").html(resposta);
	        }
	               
	    });	
	});
    
    $("#btnRestaurar").click(function(){
		
		
	});
});


function restauraPendencias(){
	
	var pend = $('#pendencias').serialize();
	
	$.post('/admin/venda/restaurapendencias?'+pend, {
		empresa			: $("input[name=empresa]").val(),
		dataini			: $("input[name=dataini]").val(),
    	datafim			: $("input[name=datafim]").val(),
    	canceladas		: $("input[name=canceladas]:checked").val()
    },
    function(resposta) {
    	
		if(resposta == false){
			jAlert('Erro ao restaurar pendências! Tente novamente.', 'Erro!');
		}else{
			jAlert('Pendências restauradas com sucesso!', 'Sucesso!');
			
			$("#resultado").html('<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i>');
    		
    		$.post('/admin/venda/buscapendbaixadas', {
    			empresa			: $("input[name=empresa]").val(),
    			dataini			: $("input[name=dataini]").val(),
    	    	datafim			: $("input[name=datafim]").val(),
    	    	canceladas		: $("input[name=canceladas]:checked").val()
    	    },
    	    function(resposta) {
    			if(resposta == false){
    				jAlert('Erro ao buscar pendências! Tente novamente.', 'Erro!');
    			}else{
    				$("#resultado").html(resposta);
    	        }    	               
    	    });			
        }               
    });	
}
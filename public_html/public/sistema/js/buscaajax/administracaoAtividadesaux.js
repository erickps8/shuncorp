$(function(){   
	
	$("#divform").ready(function(){
		$("#divform").show();
		$("#loading").hide();
	});
	
    $("#btngravaativ").click(function(){
		if($("#atividade").val() == ""){
			$("#atividade").css({border:'1px solid #f00'});
		}else if($("input[name=previsao]").val() == ""){
			$("input[name=previsao]").css({border:'1px solid #f00'});
		}else{
			
			$("#divform").hide();
    		$("#loading").show();
			
			var data = $('#funcionarios').serialize();
			
			$.post('/admin/administracao/gravaatividade?'+data, {
				titulo			: $("input[name=titulo]").val(),
		    	atividade     	:$("#atividade").val(),
		    	previsao		: $("input[name=previsao]").val()		    	
		    },
		    function(resposta) {
				
				if(resposta == false){	
					jAlert('Erro ao salvar a atividade! Tente novamente.', 'Erro!');
				}else{
					atualizaPainel();
					jAlert('Atividade salva com sucesso!', 'Sucesso!');
		        }
		               
		    });	
		}
	});
	
    //--- exibe campos comentarios/inicio atividades --------------------------------------------------------------
    $("#btniniciaativ").click(function(){
    	$("#divprevisao").toggle("slow");
    	
    	if( $('#divcomentario').is(':visible') ) {
    		$("#divcomentario").toggle("slow");
		}
    	
    	if( $('#divfechar').is(':visible') ) {
    		$("#divfechar").toggle("slow");
		}
    });
    
    $("#btncomentativ").click(function(){
    	$("#divcomentario").toggle("slow");
    	
    	if( $('#divprevisao').is(':visible') ) {
    		$("#divprevisao").toggle("slow");
		}
    	
    	if( $('#divfechar').is(':visible') ) {
    		$("#divfechar").toggle("slow");
		}
    });
    
    $("#btnconcativ").click(function(){
    	$("#divfechar").toggle("slow");
    	
    	if( $('#divprevisao').is(':visible') ) {
    		$("#divprevisao").toggle("slow");
		}
    	
    	if( $('#divcomentario').is(':visible') ) {
    		$("#divcomentario").toggle("slow");
		}
    });
    
    //--- grava comentarios -------------------------------------------------------------------------------------------
    $("#btnsalvacomentaativ").click(function(){
		if($("#comentario").val() == ""){
			$("#comentario").css({border:'1px solid #f00'});
		}else{
			
			$("#divform").hide();
    		$("#loading").show();
			
			$.post('/admin/administracao/gravacomentario', {
				idatividade		: $("#idatividade").val(),
				comentario     	: $("#comentario").val()		    	
		    },
		    function(resposta) {
				
				if(resposta == false){	
					jAlert('Erro ao salvar o comentário! Tente novamente.', 'Erro!');
				}else{
					atualizaPainel();
					jAlert('Comentário salvo com sucesso!', 'Sucesso!');
		        }
		               
		    });	
		}
	});
    
    //--- grava inico execucao atividade --------------------------------------------------------------------------------------
    $("#btnsalvainiciaativ").click(function(){
		if($("#dtprevisao").val() == ""){
			$("#dtprevisao").css({border:'1px solid #f00'});
		}else{
			
			$("#divform").hide();
    		$("#loading").show();
			
			$.post('/admin/administracao/gravainicioatividade', {
				idatividade		: $("#idatividade").val(),
				dtprevisao     	: $("#dtprevisao").val()		    	
		    },
		    function(resposta) {
				
				if(resposta == false){	
					jAlert('Erro ao salvar a data inicio da atividade! Tente novamente.', 'Erro!');
				}else{
					atualizaPainel();
					jAlert('Salvo com sucesso!', 'Sucesso!');
		        }
		               
		    });	
		}
	});
    
    //--- grava conclusao atividade --------------------------------------------------------------------------------------
    $("#btnConcconfativ").click(function(){
			
		$("#divform").hide();
		$("#loading").show();
		
		$.post('/admin/administracao/fechaatividade', {
			idatividade		: $("#idatividade").val()		    	
	    },
	    function(resposta) {
			
			if(resposta == false){	
				jAlert('Erro ao finalizar a atividade! Tente novamente.', 'Erro!');
			}else{
				atualizaPainel();
				jAlert('Atividade finalizada com sucesso!', 'Sucesso!');
	        }
	               
	    });	
	});
    
    //-- fechar a atividade ------------------------------------------------------------------------------------------------
    $("#btnFecharativ").click(function(){
    	$("#divfechar").toggle("slow");
    	
    	if( $('#divprevisao').is(':visible') ) {
    		$("#divprevisao").toggle("slow");
		}
    	
    	if( $('#divcomentario').is(':visible') ) {
    		$("#divcomentario").toggle("slow");
		}
    });
    
    $("#btnFecharconfativ").click(function(){
			
		$("#divform").hide();
		$("#loading").show();
		
		$.post('/admin/administracao/encerraratividade', {
			idatividade		: $("#idatividade").val()		    	
	    },
	    function(resposta) {
			
			if(resposta == false){	
				jAlert('Erro ao fechar a atividade! Tente novamente.', 'Erro!');
			}else{
				atualizaPainel();
				jAlert('Atividade fechada com sucesso!', 'Sucesso!');
	        }
	               
	    });	
	});
    
    $("#btnAbrirativ").click(function(){
		
		$("#divform").hide();
		$("#loading").show();
		
		$.post('/admin/administracao/reabriratividade', {
			idatividade		: $("#idatividade").val()		    	
	    },
	    function(resposta) {
			
			if(resposta == false){	
				jAlert('Erro ao reabrir a atividade! Tente novamente.', 'Erro!');
			}else{
				atualizaPainel();
				jAlert('Atividade reaberta com sucesso!', 'Sucesso!');
	        }
	               
	    });	
	});
    
});



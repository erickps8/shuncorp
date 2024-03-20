$(function() {
	$("#btnbusca").click(function() {	
		$('#resultado').html('<div style="padding: 10px; border: 1px solid #d5d5d5;"><img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
		$('#resultado').show();
		
	    $.post('/admin/relatorios/buscavendascurvaclientes', {
	    	dataini     	: $("input[name=dataini]").val(),
	    	datafim  		: $("input[name=datafim]").val(),
	    	representante	: $('select[name=representante] option:selected').val(),
			televenda		: $('select[name=televenda] option:selected').val(),
			cliente			: $('select[name=cliente] option:selected').val(),
			faturados		: $("input[name=faturados]:checked").val()
	    },
	    function(resposta) {
			
			if(resposta == "erro"){	
				jAlert('Erro ao gerar o relatório! Tente novamente.', 'Erro!');
			}else{
				$('#resultado').html(resposta);
	        }	               
	    });	
	});
	
	$("#exportar").click(function() {	
		
		window.location='/admin/relatorios/buscavendascurvaclientesexp/'+
	    	'dataini/'+$("input[name=dataini]").val().replace('/','-').replace('/','-')+
	    	'/datafim/'+$("input[name=datafim]").val().replace('/','-').replace('/','-')+
	    	'/cliente/'+$('select[name=cliente] option:selected').val()+
	    	'/representante/'+$('select[name=representante] option:selected').val()+
	    	'/televenda/'+$('select[name=televenda] option:selected').val()+
	    	'/faturados/'+$("input[name=faturados]:checked").val();
		
	});
	
	$("#btnbuscacusto").click(function() {	
		$('#resultado').html('<div style="padding: 10px; border: 1px solid #d5d5d5;"><img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Aguarde...</i></div>');
		$('#resultado').show();
		
	    $.post('/admin/relatorios/buscavendascusto', {
	    	dtini     	: $("input[name=dtini]").val(),
	    	dtfim  		: $("input[name=dtfim]").val()
	    },
	    function(resposta) {
			
			if(resposta == "erro"){	
				jAlert('Erro ao gerar o relatório! Tente novamente.', 'Erro!');
			}else{
				$('#resultado').html(resposta);
	        }
	               
	    });	
	});
		
	$('#buscaPlcontas').click(function(){
		$.ajax({
	    	url: '/admin/relatorios/buscavendascustoplcontas',
	    	success: function(data) {
	    		jModal(data, "Plano de contas", 600, 600);
	    	}
	    });
	});
	
	$('#btnSalvaplcontas').live('click', function(){
		var campos = $('#planoContas').serialize();
		
		$.ajax({
	    	url: '/admin/relatorios/salvarvendascustoplcontas?'+campos,
	    	success: function(data) {
	    		if(data == 'erro'){
	    			jAlert('Erro ao gravar os planos de contas! Tente novamente.', 'Erro!');
	    		}else{
	    			jAlert('Planos de contas salvas com sucesso!', 'Sucesso!');
	    		}
	    	}
	    });
	});
	
	$('.cliente').live('mouseover', function(){
		var emp = $(this).attr('rel');
		$.ajax({
	    	url: '/admin/relatorios/buscadesccli/cliente/'+emp,
	    	success: function(data) {
	    		jModal(data, "Prazos e descontos", '', 100);
	    	}
	    });
		
	});
		
});

function buscaMes(dtini, dtfim, cli, ano){	
	
	$.ajax({
    	url: '/admin/relatorios/buscavendascurvames/dataini/'+dtini+'/datafim/'+dtfim+'/cliente/'+cli+'/ano/'+ano,
    	success: function(data) {
    		jModal(data, "Curva de vendas", '', 100);
    	}
    });	
}


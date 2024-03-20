$(function(){
	$('select[name=periodo]').change(function(){
		
		if($("select[name=periodo] option:selected").val() != '0'){
			$('#resultado').html('<div style="border: 1px solid #d5d5d5; padding: 20px; margin-top: 10px"><img src="/public/sistema/imagens/loaders/loader6.gif" alt="Carregando"> <i>Aguarde, buscando comissões...</i></div>');
			
			$.post('/admin/relatorios/buscafinanceirocomissoes', { periodo : $("select[name=periodo] option:selected").val() },
		    function(resposta) {
				
				if(resposta == "erro"){	
					jAlert('Erro ao buscar as comissões! Tente novamente.', 'Erro!');
				}else{
					$('#resultado').html(resposta);
				}
			}); 
		}
	});
	
});

function gerarPagamento(id, per, val){
	jConfirm('Gerar pagamento da comissão?', 'Confirme', function(r) {
		if(r==true){
			
			$.post('/admin/relatorios/gerarpagcomissao', { 
				rep 	: id,
				periodo : per,
				valor   : val
			},
		    function(resposta) {
				
				if(resposta == "erro"){	
					jAlert('Erro ao gerar o pagamento! Tente novamente.', 'Erro!');
				}else{
					$("select[name=periodo]").trigger("change");
				}
			});
			
		}
	});
}

function gerarPagamentotelvendas(id, per, val, tipo){
	jConfirm('Gerar pagamento da comissão?', 'Confirme', function(r) {
		if(r==true){
			
			$.post('/admin/relatorios/gerarpagcomissao', { 
				ven 	: id,
				periodo : per,
				valor   : val,
				tp		: tipo
			},
		    function(resposta) {
				
				if(resposta == "erro"){	
					jAlert('Erro ao gerar o pagamento! Tente novamente.', 'Erro!');
				}else{
					$("select[name=periodo]").trigger("change");
				}
			});
			
		}
	});
}

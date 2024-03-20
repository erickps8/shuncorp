$(function() {
	$('#painel').html('<img src="/public/sistema/imagens/loaders/loader6.gif"> <i>Carregando paineis ...</i>');
	
	$.ajax({
		url:'/admin/index/buscapaineis',
	    success: function(retorno){
	    	if(retorno == 'erro'){
	    		jAlert('Erro ao buscar paineis do seu perfil! Recarregue a página.', 'Erro!');
	    	}else{
	    		$('#painel').html(retorno);
	    	}
	    }
	});	
});


function buscaPalete(invoice){	
	 $.ajax({
    	url: "/admin/kang/buscapalete/ped/"+invoice,
    	success: function(data) {
    		jModal(data, "Pallete", 700, 300);
    		
    		$('.inteiro').mask('99999999999999999999999'); 
    		$('.moedacinco').mask('000.000.000,000', {reverse: true});
    	}
    });	
}

$(function(){
	
	$("#contentLeft td").sortable({ 
		opacity: 0.6, 
		cursor: 'move', 
		update: function() {
			$("#contentRight").html('<img alt="Gravado" src="/public/sistema/imagens/loaders/loader6.gif" id="gravado" width="15px"> Aguarde ...');
				
			var order = $(this).sortable("serialize") + '&action=updateRecordsListings'; 
			$.post("/admin/kang/gravaordempallet", order, function(theResponse){
				window.location.reload();
			}); 															 
		}								  
	});	
	
	$(".duplicaPallet").click(function(){
		$.ajax({
			url: "/admin/kang/buscaduplicapalete/pack/"+$(this).attr('rel'),
	    	success: function(data) {
	    		jModal(data, "Replicar palletes", 350, 300);	    		
	    	}
	    });
	});
		
	$("#replicarPallet").live('click', function(){
		var maxPack	 = $("input[name=maxPack]").val();
		var replica  = $("input[name=replica]").val();
		var idpack	 = $("input[name=pack]").val();
		
		if((replica == "") || (replica == "0")){
			replica = 1;
		} 
		
		replica = parseInt(replica);
		maxPack = parseInt(maxPack);
		
		if(replica > maxPack){
			$('#resReplica').html('O máximo permitido é '+maxPack);
		}else{
			$.post('/admin/kang/packcopia', {
				replica			: replica,
		    	idpack      	: idpack		    	
		    },
		    function(resposta) {
				if(resposta == false){	
					jAlert('Erro ao replicar pallet! Tente novamente.', 'Erro!');
				}else{
					jAlert('Pallets replicados com sucesso!', 'Sucesso!', function(){
						window.location = window.location; 
					});
		        }      
		    });			
		}
		
		
	});
	
	$("#removetodos").click(function(){
		jConfirm('Você deseja remover TODOS os pallet?', 'Confirme', function(r) {
			if(r==true){
				$.ajax({
					url: "/admin/kang/removeallpacklist/ped/"+$("#ped").val(),
			    	success: function(data) {
			    		jAlert('Pallets removidos com sucesso!', 'Sucesso!', function(){
							window.location = window.location; 
						});    		
			    	}
			    });
			}
		});
	});
	
	$("#btnFinaliza").click(function(){
		jConfirm('Você deseja finalizar a edição dos pallet?', 'Confirme', function(r) {
			if(r==true){
				window.location = '/admin/kang/finalizaplvenda/invoice/'+$("#pedmd5").val();
			}
		});
	});
	
	$(".rem-pack").click(function(){
		var pack = $(this).attr('rel');
		
		jConfirm('Você deseja remover esse pallet?', 'Confirme', function(r) {
			if(r==true){
				window.location='/admin/kang/removerpacklist/pack/'+pack+'/ped/'+$("#pedmd5").val();
			}
		});
	});
});

$("#btnGerarpallet").live('click', function(){
	
	var pacotes = $("#qtpacotes").val();
	var vlprod = 0;
	var verqt  = 0;
	
	$(".qtpacote").each(function(){
		var obj = $(this);
				
		if(obj.val() != ""){
			vlprod = 1; // valida se existem produtos selecionados 
			
			id = obj.attr('name').split('_');
			
			if(obj.attr('data-qtpack') >= (obj.val() * pacotes)){ 
				if($("#pc_"+id[1]).val() == ""){
					alert('O campo PKGS não pode ficar em branco.');
					return false;	
				}				
				
			}else{
				alert('A quantidade não pode ser superior a disponível.');
				verqt=1;
				return false;
			}
		}
	});
	
	if(vlprod == 0){
		alert('Selecione um produto!');
		return false;
	}else if($("#gw").val() == ""){
		alert('O peso bruto não pode ficar em branco!');
		return false;
	}else if($("#nw").val() == ""){
		alert('O peso líquido não pode ficar em branco!');
		return false;
	}else if($("#comprimento").val() == ""){
		alert('O comprimento não pode ficar em branco!');
		return false;
	}else if($("#largura").val() == ""){
		alert('A largura não pode ficar em branco!');
		return false;
	}else if($("#altura").val() == ""){
		alert('A altura não pode ficar em branco!');
		return false;
	}else if(verqt == 0){
		$("#cad").submit();
	}
	
});
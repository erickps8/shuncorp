$(function(){

	//-- atualiza produto ------------------------------------------
	$("#btnAtualizarproduto").live('click', function(){
		
		var codigo = $(this).attr('rel');
		
		jConfirm('Você deseja atualizar a base dos produtos?', 'Confirme', function(r) {
			if(r==true){
				etapa1();								
			}
		});
	});
	
	function etapa1() {
		$("#tabFabricantes").addClass("green italic");
		$("#loaderFabricantes").show();
		
		$.ajax({
			url: "/admin/sincronismo/atualizaprodutos/etapa/1",
			success: function(data) {
				$("#loaderFabricantes").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
				  
				if(data == true){
					$("#tabFabricantes").removeClass("italic").addClass("normal");
					etapa2();
				}else{
					$("#tabFabricantes").removeClass("green italic").addClass("red normal");
					
					jAlert("Erro ao tentar atualizar a base de produtos! Tente novamente. <img style='cursor: pointer' id='btnDet' title='' src='/public/sistema/imagens/icons/dark/alert2.png' >"+
						"<div style='display: none' id='divDet'>"+data+"</div>", "Erro!");
				}
			}
		});		
	};
	
	function etapa2(){
		$("#tabMontadoras").addClass("green italic");
		$("#loaderMontadoras").show();
		
		$.ajax({
			url: "/admin/sincronismo/atualizaprodutos/etapa/2",
			success: function(data) {
				  
				if(data == true){
					$("#tabMontadoras").removeClass("italic").addClass("normal");
					$("#loaderMontadoras").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
					
					$("#tabVeiculos").addClass("green italic");
					$("#loaderVeiculos").show();	
					
					setTimeout( function() 
					{
						$("#tabVeiculos").removeClass("italic").addClass("normal");
						$("#loaderVeiculos").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
						
						$("#tabGrupos").addClass("green italic");
						$("#loaderGrupos").show();				
					}, 800);
					
					setTimeout( function() 
					{
						$("#tabGrupos").removeClass("italic").addClass("normal");
						$("#loaderGrupos").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
						
						$("#tabNcm").addClass("green italic");
						$("#loaderNcm").show();						
					}, 1200);
					
					setTimeout( function() 
					{
						$("#tabNcm").removeClass("italic").addClass("normal");
						$("#loaderNcm").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
						
						etapa3();
						
					}, 1800);
					
				}else{
					$("#tabVeiculos").removeClass("green italic").addClass("red normal");
					jAlert("Erro ao tentar atualizar a base de produtos! Tente novamente. <img style='cursor: pointer' id='btnDet' title='' src='/public/sistema/imagens/icons/dark/alert2.png' >"+
						"<div style='display: none' id='divDet'>"+data+"</div>", "Erro!");
				}
			}
		});		
	};
	
	function etapa3(){
		$("#tabProdutos").addClass("green italic");
		$("#loaderProdutos").show();
		
		$.ajax({
			url: "/admin/sincronismo/atualizaprodutos/etapa/3",
			success: function(data) {
				  
				if(data == true){
					$("#tabProdutos").removeClass("italic").addClass("normal");
					$("#loaderProdutos").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
					etapa4();					
				}else{
					$("#tabProdutos").removeClass("green italic").addClass("red normal");
					jAlert("Erro ao tentar atualizar a base de produtos! Tente novamente. <img style='cursor: pointer' id='btnDet' title='' src='/public/sistema/imagens/icons/dark/alert2.png' >"+
						"<div style='display: none' id='divDet'>"+data+"</div>", "Erro!");
				}
			}
		});		
	};
	
	function etapa4(){
		
		$("#tabRefcruzada").addClass("green italic");
		$("#loaderRefcruzada").show();	
		
		$.ajax({
			url: "/admin/sincronismo/atualizaprodutos/etapa/4",
			success: function(data) {
				
				if(data == true){
					$("#tabRefcruzada").removeClass("italic").addClass("normal");
					$("#loaderRefcruzada").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
					
					$("#tabAplicacao").addClass("green italic");
					$("#loaderAplicacao").show();
					
					setTimeout( function() 
					{
						$("#tabAplicacao").removeClass("italic").addClass("normal");
						$("#loaderAplicacao").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
						
						$("#tabMedidas").addClass("green italic");
						$("#loaderMedidas").show();
											
					}, 400);
					
					setTimeout( function() 
					{
						$("#tabMedidas").removeClass("italic").addClass("normal");
						$("#loaderMedidas").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
												
						$("#tabComponentes").addClass("green italic");
						$("#loaderComponentes").show();	
						
					}, 1200);
					
					setTimeout( function() 
					{
						$("#tabComponentes").removeClass("italic").addClass("normal");
						$("#loaderComponentes").html("<img src='/public/sistema/imagens/icons/color/tick.png' >");
						
						jAlert("Base de produtos atualizada com sucesso!","Sucesso!");
						
					}, 1800);
					
				}else{
					$("#tabAplicacao").removeClass("green italic").addClass("red normal");
					jAlert("Erro ao tentar atualizar a base de produtos! Tente novamente. <img style='cursor: pointer' id='btnDet' title='' src='/public/sistema/imagens/icons/dark/alert2.png' >"+
						"<div style='display: none' id='divDet'>"+data+"</div>", "Erro!");
				}
			}
		});		
	};
	
	$("#btnDet").live('click', function(){
		$("#divDet").toggle();
	});
	
});
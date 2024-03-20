$(document).ready(function(){
	
	 $("input[name=janelamodal]").click( function(ev){
        ev.preventDefault();
        $(".window").hide();
 
        var id = $(this).attr("janela");
 
        var alturaTela = $(document).height();
        var larguraTela = $(window).width();

        //colocando o fundo preto
        $('#mascara').css({'width':larguraTela,'height':alturaTela});
        $('#mascara').fadeIn(10);
        $('#mascara').fadeTo("slow",0.2);
 
        var left = ($(window).width() /2) - ( $(id).width() / 2 );
        var top = ($(window).height() / 2) - ( $(id).height() / 2 );
 
        $(id).css({'top':top,'left':left});
        $(id).show();
	});
	
	$("a[name=janelamodal]").click( function(ev){
        ev.preventDefault();
        $(".window").hide();
 
        var id = $(this).attr("janela");
 
        var alturaTela = $(document).height();
        var larguraTela = $(window).width();

        //colocando o fundo preto
        $('#mascara').css({'width':larguraTela,'height':alturaTela});
        $('#mascara').fadeIn(10);
        $('#mascara').fadeTo("slow",0.2);
 
        var left = ($(window).width() /2) - ( $(id).width() / 2 );
        var top = ($(window).height() / 2) - ( $(id).height() / 2 );
 
        $(id).css({'top':top,'left':left});
        $(id).show();
	});
	 
	 
    /*$("#mascara").click( function(){
        $(this).hide();
        $(".window").hide();
    });*/
 
    
       
});

function criaModal(param){
	
	
	//$(".window").hide();

    // --- mascara -------------
    var alturaTela = $(document).height();
    var larguraTela = $(window).width();
    $('#mascara').css({'width':larguraTela,'height':alturaTela});
    $('#mascara').fadeIn(10);
    $('#mascara').fadeTo("slow",0.2);
    
	//--- posicao da janela -----------
    var left = ($(window).width() /2) - ( param['largura'] / 2 );
    var top = ($(window).height() / 2) - ( param['altura'] / 2 );

    //--- cria div -----------------------
	$('<div>') 
	.attr({id: 'jmodal' })
	.addClass('window')
	.css({ 'width': param['largura'], 'height': param['altura'], 'top':top,'left':left})
	.appendTo('#sisconteudo')
	.show(); 	

  	$('<span>').attr({id: 'titulojanelager' }).addClass('titulojanela').html(param['titulo']).appendTo('#jmodal').show();	
  	$('<br />').appendTo('#jmodal').show();	
  	$('<span>').attr({id: 'subtitulojanelager' }).addClass('subtitulojanela').html(param['subtitulo']).appendTo('#jmodal').show();
  	
  	$('<div>').attr({id: 'subtexto' }).css({ 'margin-top': '45px', 'text-align': 'center'}).appendTo('#jmodal').show();
  	
  	if(param['tpj']==1){  
  		$('<div>').attr({id: 'barraprogresso' })
  		.appendTo('#subtexto').show();
  	}else{
	  	if(param['btsim']==1){
		  	$('<a>')
		  	.attr({href:'javascript:void(0)'})
		  	.addClass('botaoconfirma')
		  	.html(' &nbsp; OK &nbsp; ')
		  	.click(function () {
		  		
		    })
		  	.appendTo('#subtexto')
		  	.show();
	  	}
	  	
	  	if(param['btnao']==1){
		  	$('<a>')
		  	.attr({href:'javascript:void(0)'})
		  	.addClass('botaocancela')
		  	.html('Cancelar')
		  	.click(function () {
		  		removeModal();
		    })
		  	.appendTo('#subtexto')
		  	.show();
	  	}
  	}
}

function removeModal(){
    $("#mascara").hide();
    $(".window").remove();
}

function exibeModal(id){
	
    $(".window").hide();
 
    var alturaTela = $(document).height();
    var larguraTela = $(window).width();

        //colocando o fundo preto
    $('#mascara').css({'width':larguraTela,'height':alturaTela});
    $('#mascara').fadeIn(10);
    $('#mascara').fadeTo("slow",0.2);
 
    var left = ($(window).width() /2) - ( $(id).width() / 2 );
    var top = ($(window).height() / 2) - ( $(id).height() / 2 );
    
    $(id).css({'top':top,'left':left});
    $(id).show();
	
}

function exibeModalclose(id){
	
    $(".window").hide();
 
    var alturaTela = $(document).height();
    var larguraTela = $(window).width();

        //colocando o fundo preto
    $('#mascara').css({'width':larguraTela,'height':alturaTela});
    $('#mascara').fadeIn(10);
    $('#mascara').fadeTo("slow",0.2);
 
    var left = ($(window).width() /2) - ( $(id).width() / 2 );
    var top = ($(window).height() / 2) - ( $(id).height() / 2 ); 
    
    
    $(id).css({'top':top,'left':left});
    $(id).show();
	
}


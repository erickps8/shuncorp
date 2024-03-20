/* Recebe parametros e centraliza os Pop Ups na tela */
function PopUpCentralizado(nomepagina, titulopagina, w, h, scroll) {
  var winl = (screen.width - w) / 2;
  var wint = (screen.height - h) / 2;
  winprops = 'height=' + h + ', width = ' + w + ', top = ' + wint + ', left = ' + winl + ', scrollbars = ' + scroll + ', location=no, status=no';
  win = window.open(nomepagina, titulopagina, winprops);
  if (parseInt(navigator.appVersion)  >= 4) {
      win.window.focus();
  }
}

function abrePopupobs(){
	
	var id = '#obs';

	var maskHeight = $(document).height();
	var maskWidth = $(window).width();

	$('#mask').css({'width':maskWidth,'height':maskHeight});

	$('#mask').fadeIn(10);	
	$('#mask').fadeTo("fast",0.8);	

	//Get the window height and width
	var winH = $(window).height();
	var winW = $(window).width();
          
	$(id).css('top',  winH/2-$(id).height()/2-80);
	$(id).css('left', winW/2-$(id).width()/2);

	$(id).fadeIn(20); 

}

/*----------Popups------------------------------------------*/ 

$(document).ready(function() {	

	$('input[name=modal]').click(function(e) {
		e.preventDefault();
		
		var id = $(this).attr('href');
	
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
	
		$('#mask').css({'width':maskWidth,'height':maskHeight});

		$('#mask').fadeIn(0);	
		$('#mask').fadeTo("fast",0.4);	
	
		//Get the window height and width
		var winH = $(window).height();
		var winW = $(window).width();
              
		$(id).css('top',  winH/2-$(id).height()/2-80);
		$(id).css('left', winW/2-$(id).width()/2);
	
		$(id).fadeIn(0); 
	
	});
	
	$('.window .close').click(function (e) {
		e.preventDefault();
		
		$('#mask').hide();
		$('.window').hide();
	});		
	
	$('#mask').click(function () {
		$(this).hide();
		$('.window').hide();
	});				
});


$(function() {
	
	//===== Custom single file input =====//
	
	$("input.fileInput").filestyle({ 
		imageheight : 26,
		imagewidth : 89,
		width : 300
	});
	

	$('.fileInput').live('change', function(){
		$('#'+this.name).val($(this).val());							
	});
	
	//===== Dual select boxes =====//
	$.configureBoxes();
	
	
	//===== Time picker =====//
	
	$('.timepicker').timeEntry({
		show24Hours: true, // 24 hours format
		showSeconds: false, // Show seconds?
		spinnerImage: "/public/sistema/imagens/ui/spinnerUpDown.png", // Arrows image
		spinnerSize: [17, 26, 0], // Image size
		spinnerIncDecOnly: true // Only up and down arrows
	});
	
	//===== Alert windows =====//
	/*
	$(".bAlert").click( function() {
		jAlert('This is a custom alert box. Title and this text can be easily editted', 'Alert Dialog Sample');
	});
	
	$(".bConfirm").click( function() {
		jConfirm('Can you confirm this?', 'Confirmation Dialog', function(r) {
			jAlert('Confirmed: ' + r, 'Confirmation Results');
		});
	});
	
	$(".bPromt").click( function() {
		jPrompt('Type something:', 'Prefilled value', 'Prompt Dialog', function(r) {
			if( r ) alert('You entered ' + r);
		});
	});
	
	$(".bHtml").click( function() {
		jAlert('You can use HTML, such as <strong>bold</strong>, <em>italics</em>, and <u>underline</u>!');
	});

	 */
	
	//===== Accordion =====//		
	
	$('div.menu_body:eq(0)').show();
	$('.acc .head:eq(0)').show().css({color:"#2B6893"});
	
	$(".acc .head").click(function() {	
		$(this).css({color:"#2B6893"}).next("div.menu_body").slideToggle(300).siblings("div.menu_body").slideUp("slow");
		$(this).siblings().css({color:"#404040"});
	});
	
		
	//===== ToTop =====//

	$().UItoTop({ easingType: 'easeOutQuart' });	
	
		
	
	//===== ToTop =====//

	$().UItoTop({ easingType: 'easeOutQuart' });	
	

	//===== Form elements styling =====//
	
	$('form').jqTransform({imgPath:'../images/forms'});
	
	
	//===== Form validation engine =====//

	$("#valid").validationEngine();
	
	
	
	//===== Datepickers =====//

	//defaultDate: +7,
	$( ".datepicker" ).datepicker({ 
		autoSize: true,
		dateFormat: 'dd/mm/yy',
		dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
		dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
		dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
		monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
		nextText: 'Próximo',
	    prevText: 'Anterior'
	});	

	$( ".datepickerInline" ).datepicker({ 
		defaultDate: +7,
		autoSize: true,
		appendText: '(dd-mm-yyyy)',
		dateFormat: 'dd-mm-yy',
		numberOfMonths: 1
	});	


	
		
	//===== Tooltip =====//
		
	$('.leftDir').tipsy({fade: true, gravity: 'e'});
	$('.rightDir').tipsy({fade: true, gravity: 'w'});
	$('.topDir').tipsy({fade: true, gravity: 's'});
	$('.botDir').tipsy({fade: true, gravity: 'n'});

		
	//===== Information boxes =====//
		
	$(".hideit").click(function() {
		$(this).fadeOut(400);
	});
	

	

	//===== Left navigation submenu animation =====//	
		
	$("ul.sub li a").hover(function() {
	$(this).stop().animate({ color: "#3a6fa5" }, 400);
	},function() {
	$(this).stop().animate({ color: "#494949" }, 400);
	});
	
	
		
	
	//===== Tabs =====//
	
	$.fn.simpleTabs = function(){ 
	
		//Default Action
		$(this).find(".tab_content").hide(); //Hide all content
		$(this).find("ul.tabs li:first").addClass("activeTab").show(); //Activate first tab
		$(this).find(".tab_content:first").show(); //Show first tab content
	
		//On Click Event
		$("ul.tabs li").click(function() {
			$(this).parent().parent().find("ul.tabs li").removeClass("activeTab"); //Remove any "active" class
			$(this).addClass("activeTab"); //Add "active" class to selected tab
			$(this).parent().parent().find(".tab_content").hide(); //Hide all tab content
			var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
			$(activeTab).show(); //Fade in the active content
			return false;
		});
	
	};//end function

	$("div[class^='widget']").simpleTabs(); //Run function on any div with class name of "Simple Tabs"


	//===== User nav dropdown =====//		

	$('.dd').click(function () {
		$('ul.menu_body').slideToggle(100);
	});
	
	$('.configuracoes').click(function () {
		$('ul.menu_config').slideToggle(100);
	});
	
	$('.acts').click(function () {
		$('ul.actsBody').slideToggle(100);
	});
	
	
	//===== Collapsible elements management =====//
	
	$('.active').collapsible({
		defaultOpen: 'current',
		cookieName: 'nav',
		speed: 300
	});
	
	$('.exp').collapsible({
		defaultOpen: 'current',
		cookieName: 'navAct',
		cssOpen: 'active',
		cssClose: 'inactive',
		speed: 300
	});

	$('.opened').collapsible({
		defaultOpen: 'opened,toggleOpened',
		cssOpen: 'inactive',
		cssClose: 'normal',
		speed: 200
	});

	$('.closed').collapsible({
		defaultOpen: '',
		cssOpen: 'inactive',
		cssClose: 'normal',
		speed: 200
	});
	
	$('.data').mask('11/11/1111');
	$('.mesano').mask('11/1111');
    $('.inteiro').mask('99999999999999999999999');
    $('.cep').mask('99999-999');	  
    $('.moeda').mask('000.000.000.000.000,00', {reverse: true});  
    $('.moedatres').mask('000.000.000.000.000,000', {reverse: true});
    $('.moedacinco').mask('000.000.000.000.000,00000', {reverse: true});
    $('.cpf').mask('999.999.999-99');
    $('.cnpj').mask('99.999.999/9999-99');
    $('.peso').mask('0000,00000', {reverse: true});
    $('.fone').mask('(00) 000000000');
    $('.alpha').mask('AAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
    $('.ncm').mask('9999.9999');
    $('.ano').mask('9999');
    $('.motor').mask('9.9');
    
});


function moedaNegativo(w,e,m,r,a){
    
    // Cancela se o evento for Backspace
    if (!e) var e = window.event;
    if (e.keyCode) code = e.keyCode;
    else if (e.which) code = e.which;
    
    // Variáveis da função
    var txt  = (!r) ? w.value.replace(/[^\d]+/gi,'') : w.value.replace(/[^\d]+/gi,'').reverse();
    var mask = (!r) ? m : m.reverse();
    var pre  = (a ) ? a.pre : "";
    var pos  = (a ) ? a.pos : "";
    var ret  = "";

    if(code == 9 || code == 8 || txt.length == mask.replace(/[^#]+/g,'').length) return false;

    // Loop na máscara para aplicar os caracteres
    for(var x=0,y=0, z=mask.length;x<z && y<txt.length;){
            if(mask.charAt(x)!='#'){
                    ret += mask.charAt(x); x++;
            } else{
                    ret += txt.charAt(y); y++; x++;
            }
    }
    
    // Retorno da função
    ret = (!r) ? ret : ret.reverse();
       
    if (w.value.match("-")){
        w.value = "-"+ret+pos;
    }else{
        w.value = pre+ret+pos;
    }
}

//Novo método para o objeto 'String'
String.prototype.reverse = function(){
    return this.split('').reverse().join('');
};

function float2moeda(num) {

	   x = 0;

	   if(num<0) {
	      num = Math.abs(num);
	      x = 1;
	   }
	   if(isNaN(num)) num = "0";
	      cents = Math.floor((num*100+0.5)%100);

	   num = Math.floor((num*100+0.5)/100).toString();

	   if(cents < 10) cents = "0" + cents;
	      for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
	         num = num.substring(0,num.length-(4*i+3))+'.'
	               +num.substring(num.length-(4*i+3));
	      ret = num + ',' + cents;
	      if (x == 1) ret = ' - ' + ret;return ret;

}

function moeda2float(moeda){
	moeda = moeda.replace(".","");
	moeda = moeda.replace(",",".");
	return parseFloat(moeda);

}
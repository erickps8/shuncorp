/**
Cleiton Silva Barbosa
03-02-2011
Configuracao do slide da pagina inicial
*/
jQuery(function( $ ){
	
	$.easing.backout = function(x, t, b, c, d){
		var s=1.70158;
		return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
	};
	
	$('#screen').scrollShow({
		view:'#view',
		content:'#images',
		easing:'backout',
		wrappers:'link,crop',
		navigators:'a[id]',
		navigationMode:'sr',
		circular:true,
		start:0
	});
});


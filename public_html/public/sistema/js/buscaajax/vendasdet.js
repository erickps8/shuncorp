VendasDet = {
	init: function (){
		VendasDet.eventos();
	},
	eventos: function () {
		$(".jqTransformCheckbox").click(function () {
			VendasDet.somaFrete();
		});

		$("#frete").blur(function () {
			VendasDet.somaFrete();
		})
	},
	somaFrete: function () {
		if($("#somaFrete").attr('checked') == true){
			var frete = moeda2float($("#frete").val());
			var totalVenda = parseFloat($("#totalVendaHidden").val());

			totalVenda += frete;
		}else{
			var totalVenda = parseFloat($("#totalVendaHidden").val());
		}

		var moeda = $("#moeda").val();
		$("#totalVenda").html(moeda + ' ' + float2moeda(totalVenda));
	}
}

$(document).ready(function(){
	VendasDet.init();
});


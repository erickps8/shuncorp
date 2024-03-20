FinRelatorio = {
	init: function () {
		FinRelatorio.eventos();
	},
	eventos: function () {
		FinRelatorio.checkFil();

		$("#tpRelatorio").change(function () {
			FinRelatorio.checkFil();
		});

		$("#btnPesq").click(function () {
			if($("#tpRelatorio option:selected").val() == '0') {
				jAlert('Selecione um tipo de relatório!', 'Erro!');
			}else{
				$("#form").submit();
			}
		});

		$("#btnImprimir").click(function () {
			var dtIni = $('#dtiniconc').val().replace('/', '-').replace('/', '-');
			var dtFin = $('#dtfimconc').val().replace('/', '-').replace('/', '-');

			var tpRelatorio = $("#tpRelatorio option:selected").val();

			if(tpRelatorio == 1) {
				window.open('/admin/financeiro/rebateimp/dtiniconc/' + dtIni + '/dtfimconc/' + dtFin + '/idInvoice/' + $('#idInvoice').val());
			}

			if(tpRelatorio == 2) {
				window.open('/admin/financeiro/freteimp' +
					'/dtiniconc/' + dtIni +
					'/dtfimconc/' + dtFin +
					'/idInvoice/' + $('#idInvoice').val() +
					'/buscasit/' + $('#buscasit').val() +
					'/buscafor/' + $('#buscafor').val() +
					'/buscacli/' + $('#buscacli').val()
				);
			}
		});
	},
	checkFil: function () {
		var tpRelatorio = $("#tpRelatorio option:selected").val();

		if(tpRelatorio == 2) {
			$(".fil-frete").show();
		}else{
			$(".fil-frete").hide();
			$("#buscasit, #buscafor, #buscacli").val(0);
		}
	},
}

$(function(){
	FinRelatorio.init();
});

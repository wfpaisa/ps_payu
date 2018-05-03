$(document).ready(() => {
	console.log(PayURegisterOrder);
	$(document).on("click", "#payment-confirmation button", function(event) { 
		// event.preventDefault();

		// // Registra el pedido
		// $.get( PayURegisterOrder, function( data ) {
		// 	$( ".result" ).html( data );
		// });
		
		window.open(PayURegisterOrder);
	});
})
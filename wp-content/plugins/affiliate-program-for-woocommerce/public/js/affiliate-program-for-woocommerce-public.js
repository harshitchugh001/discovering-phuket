(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$( document ).ready(
		function(){
			$( '#apf_datatable' ).DataTable();
			/*create clipboard */
			var btns = document.querySelectorAll( '.apf_btn_copy' );
			var message = '';
			var clipboard = new ClipboardJS( btns );
			var ctxB = $( "#myChart" );
			if ( ctxB.length > 0 ) {
				  var ctxB = ctxB[0].getContext( '2d' );
				var myBarChart = new Chart(
					ctxB,
					{
						type: 'bar',
						data: {
							labels: [afp_js.total_earning_label, afp_js.total_balance_label, afp_js.total_refund_label ],
							datasets: [{
								label: afp_js.main_label,
								data: [afp_js.total_earn, afp_js.total_balance, afp_js.total_refund,],
								backgroundColor: [
								  // 'rgba(255, 99, 132, 0.2)',
								  // 'rgba(54, 162, 235, 0.2)',
								  'rgba(255, 206, 86, 0.2)',
								  'rgba(75, 192, 192, 0.2)',
								  'rgba(153, 102, 255, 0.2)',
								  // 'rgba(255, 159, 64, 0.2)'
								  ],
								borderColor: [
								  // 'rgba(255,99,132,1)',
								  // 'rgba(54, 162, 235, 1)',
								  'rgba(255, 206, 86, 1)',
								  'rgba(75, 192, 192, 1)',
								  'rgba(153, 102, 255, 1)',
								  // 'rgba(255, 159, 64, 1)'
								  ],
								borderWidth: 1
							}]
						},
						options: {
							scales: {
								yAxes: [{
									ticks: {
										beginAtZero: true
									}
								}]
							}
						}
					}
				);
			}

		}
	)

})( jQuery );

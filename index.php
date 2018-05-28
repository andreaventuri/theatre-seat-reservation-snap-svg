<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Theater Map</title>

	<style>
		body {
			font-family: verdana;
		}
		h1 {
			font-size:24px;
		}
		#seats rect {
			cursor: pointer;
		}
		#seats rect.seat_selected {
			fill:#FFFF00;
		}
		#seats rect.seat_occupied {
			fill:#FF0000;
			cursor: not-allowed;
		}
		#tooltip {
			opacity: 0;
			color: #383d41;
			background-color: #e2e3e5;
			border: 2px solid #d6d8db;
			border-radius: 6px;
			position: fixed;
			padding: 10px 5px;
		}
		#tooltip p {
			font-family: tahoma;
			margin:0;
			padding:0;
		}
		#tooltip p.row1 {
			font-weight:bold;
		}
	</style>

	<script src="js/snap.svg-min.js"></script>
	<script src="js/jquery-3.3.1.min.js"></script>
	<script>
		window.onload = function () {

			var s = Snap('#mappa');

			Snap.load("theatre.svg", function(f){

				load();

				f.selectAll('#seats rect').forEach(function(el){

					el.attr({fill:'#33CC33'});

					el.click(function(ev){

						var elem = Snap.select('#'+ev.target.id);

						if( elem.data('status')=='O' )
						{
							// nothing to do
						}
						else if( elem.data('status')=='S' )
						{
							// the seat is already selected, the user wants to deselect it
							check('D', ev.target.id);
							elem.data('status', 'D');
						}
						else
						{
							// the seat is free, the user wants to select it
							check('S', ev.target.id);
							elem.data('status', 'S');
						}
					});

					el.mousemove(function(ev){

						// when mouse is over the seat, system shows an informational tooltip
						var elem = Snap.select('#'+ev.target.id);

						if( elem.data('status')!='O' )
						{
							var testo = '<p class="row1">'+elem.data('row1')+'</p>'
									  + '<p class="row2">'+elem.data('row2')+'</p>'
									  + '<p class="row3">'+elem.data('row3')+'</p>';

							$('#tooltip')
								.html(testo)
								.css("opacity", "1")
								.css("left", ev.clientX + 15 + "px")
								.css("top", ev.clientY + 15 + "px");
						}
					});

					el.mouseout(function(ev){

						// hides the tooltip
						$('#tooltip').css("opacity", "0");
					});

				});

				s.append(f);
			});
		};

		// =====================================================================

		/**
		 * Loads all the information about seats from backend
		 */
		function load()
		{
			$.ajax({
				url: "map.php",
				data: {
					'session': '<?php echo md5(uniqid(mt_rand(),true))?>',
					'id': 17
				},
				dataType: "json",
				success: function(data){

					$.each(data, function(i, obj) {

						var id = '#seat-'+obj.sector_id+'-'+obj.row+'-'+obj.number;
						var elem = Snap.select(id);

						// text for tooltip
						elem.data('row1', obj.sector+', seat '+obj.row+'-'+obj.number);
						elem.data('row2', 'Full price: &euro; '+obj.price_full);
						elem.data('row3', 'Reduced price: &euro; '+obj.price_reduced);

						if(obj.selected==1)
						{
							// seat is occupied by another user
							elem.data('status', 'O');

							$(id).addClass('seat_occupied');

							$('#selected').append('<li id="seat_'+obj.id+'">'+obj.sector+' - Seat '+obj.row+'-'+obj.number+'</li>');
						}
						else
						{
							elem.data('status', 'D');
						}
					});
				}
			});
		}

		/**
		 * Tries to select/unselect a seat for the user
		 */
		function check(action, id)
		{
			var risp = 3;

			$.ajax({
				url: "check.php",
				type: "GET",
				dataType: 'json',
				data: {
					'action': action,
					'id': id
				},
				success: function(data){

					risp = parseInt(data.result);

					if(risp==1)
					{
						if(action=='S')
						{
							$('#'+id).addClass('seat_selected');
							$('#selected').append('<li id="seat_'+data.id+'">'+data.sector+' - Seat '+data.row+'-'+data.number+'</li>');
						}
						else if(action=='D')
						{
							$('#'+id).removeClass('seat_selected');
							$('#seat_'+data.id).remove();
						}
					}
				}
			});

			return risp;
		}

	</script>
</head>
<body>

	<h1>Theatre Seat Reservation - Snap.svg</h1>

	<div id="mappa"></div>

	<h3>Selected Seats</h3>
	<ul id="selected"></ul>

	<div id="tooltip"></div>

</body>
</html>


		<script src="//rawgit.com/notifyjs/notifyjs/master/dist/notify.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$('#statuses').DataTable({
					"aaSorting": [[ 5, "desc" ]]
				})
// .yadcf([{
//						column_number: 5,
//						filter_type: "range_date",
//						filter_container_id: "external_filter_container",
//						date_format: "yy-mm-dd"
//				}])
									
				$( function() {
					$( "#datefrom_download" ).datepicker({
						dateFormat: "dd-mm-yy",
						onSelect: function(date) {
							/*$('#download-link').attr('href', function(index, val){
								val = val.replace(/&?from\=.+/, '');
								return val + '&from=' + date;
							});*/
						},
					});
					$( "#dateto_download" ).datepicker({
						dateFormat: "dd-mm-yy",
						onSelect: function(date) {
							/*$('#download-link').attr('href', function(index, val){
								val = val.replace(/&?to\=.+/, '');
								return val + '&to=' + date;
							});*/
						},
					});
					$( "#datefrom_update" ).datepicker({
						dateFormat: "dd-mm-yy",
						onSelect: function(date) {
							/*$('#download-link').attr('href', function(index, val){
							 val = val.replace(/&?from\=.+/, '');
							 return val + '&from=' + date;
							 });*/
						},
					});
					$( "#dateto_update" ).datepicker({
						dateFormat: "dd-mm-yy",
						onSelect: function(date) {
							/*$('#download-link').attr('href', function(index, val){
							 val = val.replace(/&?to\=.+/, '');
							 return val + '&to=' + date;
							 });*/
						},
					});
				});
				
				$('#download').click(function(){
					params = $('#external_filter_container').serializeAny();
					href = $(this).parent().attr('href') + '?' + params;
					window.location = href;
					return false;
				})
				
				$.fn.serializeAny = function() {
					var ret = [];
					$.each( $(this).find(':input'), function() {
						if($(this).val()){
							ret.push( encodeURIComponent($(this).attr('placeholder')) + "=" + encodeURIComponent( $(this).val() ) );
						}
					});

					return ret.join("&").replace(/%20/g, "+");
				}

                $("#menu-toggle").click(function(e) {
                    e.preventDefault();
                    $("#wrapper").toggleClass("toggled");
                });


			});
		</script>
		<div id="lock_screen">
			<div class="lock_screen_modal">
				<h3>Закрываю смену.</h3>
				<h4>Пожалуста, подождите пока перезагрузится страница.</h4>
			</div>
		</div>
	</body>
</html>
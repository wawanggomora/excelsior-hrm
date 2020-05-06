<div class="wrap">
    <h1>Global Settings</h1>

    <form method="post" class="department-form" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Department Name</th>
                <td>
                    <input style="width: 300px;" type="text" class="department_name" name="department_name" value="" />
                </td>
            </tr>
           
        </table>

        <input type="submit" name="submit">

    </form>

    <div class="department-list">
    	<div class="department-wrap"></div>
    </div>

</div>

<script type="text/javascript">
	jQuery(document).ready(function($){

		$('.department-form').on('submit', function(e){
			e.preventDefault();
			var name = $('.department_name').val();
			console.log('before ajax: ' + name);

			$.ajax({
				url: ajax.url,
				type: 'POST',
				dataType: 'json',
				data: {
					dept_name: name,
					action: 'add_department',
				},
				success: function (result) {
					console.log("Success");
					console.log(result);
				},
				error: function () {
					alert(ajax_object.ajax_url);
				}
			});

		});

		$.ajax({
			url: ajax.url,
			type: 'GET',
			dataType: 'json',
			data: {
				action: 'get_department',
			},
			success: function (result) {
				console.log('first load: ' + result);
				$.each(result, function( index, value ) {
				  $('.department-wrap').append( value.dept_name);
				});
			},
			error: function () {
				alert(ajax_object.ajax_url);
			}
		});


	});

</script>


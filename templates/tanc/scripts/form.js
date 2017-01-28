$(document).ready(function() {
	$('form[data-async]').submit( function(event) {
		event.preventDefault();
		event.stopPropagation();
		var $form = $(this);
		var $data = $form.serialize();
		$form.find('input').attr('disabled', true);
		//var $target = $($form.attr('data-target'));
		var $dialog = $($form.attr('data-dialog'));
		var method = $form.attr('method') || 'POST';
		var url = $form.attr('action') || window.location.href;

		$.ajax({
			type: $form.attr('method'),
			url:  url,
			data: $data,

			success: function(data, status) {
				var event = jQuery.Event( "ajax:success" );
				$form.trigger(event, data);

				if (data.location) {
					window.location.href = data.location;
				}
				if ($dialog) {
					$dialog.modal('hide');
				}
			},
			error: function(jqxhr, textStatus, errMessage) {
				var event = jQuery.Event( "ajax:error" );
				$form.trigger(event, jqxhr.responseJSON || {});
				$form.find('input').attr('disabled', false);
				if (!event.isDefaultPrevented()) {

					var msg = '';
					var type = 'error';
					if (jqxhr.responseJSON && jqxhr.responseJSON.sparkmsg) {
						for (midx in jqxhr.responseJSON.sparkmsg) {
							var m = jqxhr.responseJSON.sparkmsg[midx];
							msg += "<p>" + m.msg + "</p>";
							type = m.type;
						}
					} else {
						msg = 'Error communicating with the server.';
					}
					$('<div class="modal modal-danger"><div class="modal-dialog"><div class="modal-header">'+
							'<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
							msg + '</div></div></div>').modal()
				}
			},
		}).done(function (data, status) {
			$form.find('input').attr('disabled', false);
		});
	});
});


$(document).ready(function() {
	//get a list of AP
	$.get('?action=search').done(function(data) {
		console.log(data);
		var template = Handlebars.compile($('#page-template').html());
		var html    = template(data);
		$('.col-sm-12').last().append(html);
		$('#wait').remove();
	});

var modal = $('#modal-wifi-setup');

$('#content-main').on('click', '.wifiap', function(evt) {
	modal.modal('show');

	var addr = $(evt.currentTarget).data('address');
	var ssid = $(evt.currentTarget).data('ssid');
	var $form = $(modal.find('form'));
	$form.find('input[name="ssid"]').val(ssid);
	$form.find('input[name="address"]').val(addr);
});

});


$(document).ready(function() {

var modal = $('#modal-wifi-setup');

$('.wifiap').on('click', function(evt) {
	modal.modal('show');

	var addr = $(evt.currentTarget).data('address');
	var ssid = $(evt.currentTarget).data('ssid');
	var $form = $(modal.find('form'));
	$form.find('input[name="ssid"]').val(ssid);
	$form.find('input[name="address"]').val(addr);
});

});

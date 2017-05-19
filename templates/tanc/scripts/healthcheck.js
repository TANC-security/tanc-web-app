$(document).ready(function(){
	var burl = $('body').data('base-url');
	$.get(burl+'main/health/')
	.done(function(data) {
		msg = data['user-message'] || null;
		if(msg) {
			cssclass = 'info';
			if (msg.type == 'error') {
				cssclass = 'danger';
			}
			console.log(msg);
			$('.right_col').children().first().append('<div class="alert alert-'+cssclass+'">'+msg.msg+'</div>');
		}
	});
});

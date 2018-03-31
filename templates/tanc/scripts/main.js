var eventList = [];
var beep;
$(document).ready(function() {

	var burl = $('body').data('base-url');
	var kpbuf = '';
	var timeoutRef;
	var to = 650;


	beep = function(count) {
		count = parseInt(count);
		for (var x=0; x<count; x++) {
			setTimeout(function() {
				snd.play();
			}, 400 * (0+x)+1);
		}
	}

	var doneTyping = function() {
		
		if (!timeoutRef) return;
		if (kpbuf == '') return;
		timoutRef = null;
		var bufcopy = kpbuf;
		kpbuf = '';
		beepcount = bufcopy.length
		$.ajax(burl+'kp/main/send/?k='+encodeURIComponent(bufcopy))
		.done( function(data) {
			beep(beepcount);
//			console.log(data);
		});
	}
	
	$('.kp-container > button').on('click',function(e) {
		kpbuf += e.currentTarget.value;
	
		if (timeoutRef)  clearTimeout(timeoutRef);
		timeoutRef = setTimeout(function() {
			doneTyping();
		}, to);
	});


	try {
//		pollDisplay();
		wsDisplay();
	} catch (e) {
		setTimeout(onBadWs,3000);
	}
});

function showDisplayError() {
	if ($('.alert-danger').length){
		return;
	}
	$('#content-main').prepend('<div class=\"alert alert-danger\">Communication with the security panel has been interrupted.</div>');
}

function showEvent(packet) {
//	$('#content-main').prepend('<div class=\"alert alert-info\">'+msg+'<span class=\"dismiss\">x</span></div>');

	var msg = packet.description || 'Got ' + packet.qualifier + ' event code: ' +packet.code;
	new PNotify({
		text: msg,
		type: 'info',
		//hide: false,
		styling: 'bootstrap3'
	});
	eventList.push(msg);
	if (eventList.length > 10 ) {
		eventList.pop();
	}
	repaintEvents();

}

function repaintEvents() {

	$('#topnav__msglist').empty();

	for (var x = eventList.length; x > 0; x--) {
		var msg = eventList[x-1];

	$('#topnav__msglist').append(
'                    <li>' +
'                      <a>' +
'                        <span class="image"></span>' +
'                        <span>' +
'                          <span>Alert</span>' +
//'                          <span class="time">3 mins ago</span>' +
'                        </span>' +
'                        <span class="message">' +
'                          '+msg +
'                        </span>' +
'                      </a>' +
'                    </li>' +
'					');
	}
}

function pollDisplay() {
try {
	$.get(burl+'kp/main/displayBeanstalk', function(data) {
		if (!data.items) { showDisplayError(); setTimeout(pollDisplay, 1000); return;}
		var displayMsg   = data.items[0] || '';
		var line1 = line2 = '';
		for (i=0; i < 16; i++) {
			line1 += displayMsg.charAt(i);
		}
		for (i=16; i < 32; i++) {
			line2 += displayMsg.charAt(i);
		}
		line1 = line1.replace(' ', '&nbsp;');
		line2 = line2.replace(' ', '&nbsp;');

		$('.kp-view').html(line1+'<br/>'+line2);
		setTimeout(pollDisplay,1000);
		removeDisplayError();
	}).fail(function(xhr, type, status) {
		showDisplayError();
		setTimeout(pollDisplay,10000);
	});
} catch (e) {
		setTimeout(pollDisplay,3000);
}
}

function wsDisplay() {
	var burl = $('body').data('base-url');
	// Then some JavaScript in the browser:
	var burlParts = burl.split('://');
	var scheme = 'ws://';
	if (burlParts[0] == 'https') {
		scheme = 'wss://';
	}
	var conn = new WebSocket(scheme+burlParts[1]+'display/');

	conn.onmessage = function(e) {
		//console.log(e.data);
		var packet = JSON.parse(e.data) || '';
		if (packet.type == 'event') {
			showEvent(packet);
//			showEvent('Got ' + packet.qualifier + ' event code: ' +packet.code);
			return;
		}
		if (packet.type == 'display') {
			showDisplayMessage(packet);
			return;
		}

	};
	conn.onopen = function(e) {
	};

	conn.onclose = function(e) {
		setTimeout(onBadWs,1000);
	};
}

function onBadWs() {
	showDisplayError();
	wsDisplay();
}


function showDisplayMessage(packet) {
	var displayMsg   = packet.message || e.data || '';
	var line1 = line2 = '';
	for (i=0; i < 16; i++) {
		line1 += displayMsg.charAt(i);
	}
	for (i=16; i < 32; i++) {
		line2 += displayMsg.charAt(i);
	}
	line1 = line1.replace(' ', '&nbsp;');
	line2 = line2.replace(' ', '&nbsp;');

	$('.kp-view').html(line1+'<br/>'+line2);
	removeDisplayError();

	if (packet['beeps']) {
		count = parseInt(packet['beeps']);
		beep(count);
	}
}

function removeDisplayError() {
	/*
	if ($('.alert-danger').length){
		$('.alert-danger').remove();
	}
	*/
}


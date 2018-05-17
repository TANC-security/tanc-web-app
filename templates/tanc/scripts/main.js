var eventList = [];
var beep;
var badconn = 0;

var socketserver = new EventEmitter2({
});

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
	
	$('.kp-container_full > button').on('click',function(e) {
		kpbuf += e.currentTarget.value;
	
		if (timeoutRef)  clearTimeout(timeoutRef);
		timeoutRef = setTimeout(function() {
			doneTyping();
		}, to);
	});


	try {
		wsDisplay();
	} catch (e) {
		onBadWs();
	}
});

function showDisplayError() {
	if ($('.alert-danger').length){
		return;
	}
	$('#content-main').prepend('<div class=\"alert alert-danger comm-error\">Communication with the security panel has been interrupted.</div>');
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
		var packet = JSON.parse(e.data) || '';
		socketserver.emit(packet.type, packet);
	};

	conn.onopen = function(e) {
		removeDisplayError();
		badconn = 0;
	};

	conn.onclose = function(e) {
		onBadWs();
	};
}

function onBadWs() {
	showDisplayError();
	badconn++;
	var tmo =  3000 * badconn;
	if (tmo > 60000) to = 60000;
	setTimeout(wsDisplay, tmo);
}


function showDisplayMessage(packet) {
	var displayMsg   = packet.msg || '';
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

	if (packet['beeps']) {
		count = parseInt(packet['beeps']);
		beep(count);
	}
}

function removeDisplayError() {
	if ($('.alert-danger.comm-error').length){
		$('.alert-danger.comm-error').remove();
	}
}


socketserver.on('event', function(packet) {
	showEvent(packet);
});
socketserver.on('display', function(packet) {
	showDisplayMessage(packet);
});

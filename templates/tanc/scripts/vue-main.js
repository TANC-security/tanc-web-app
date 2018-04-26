
function wsDisplay2(app) {
	var burl = $('body').data('base-url');
	// Then some JavaScript in the browser:
	var burlParts = burl.split('://');
	var scheme = 'ws://';
	if (burlParts[0] == 'https') {
		scheme = 'wss://';
	}
	var conn = new WebSocket(scheme+burlParts[1]+'display/');
	var zapp = app;

	conn.onmessage = function(e) {
		var packet = JSON.parse(e.data) || '';
		if (packet.type == 'event') {
			//showEvent(packet);
			return;
		}

		if (packet.type == 'display') {
				zapp.state = packet.armed;
				/*
			if (zapp) {
				zapp.state = packet.armed;
			}
			*/
			//showDisplayMessage(packet);
			return;
		}
	};

	conn.onopen = function(e) {
	};

	conn.onclose = function(e) {
		setTimeout(onBadWs,1000);
	};
}

var app = new Vue({
	el: '#main_main',
	data: {
		//message: 'Hello Vue!',
		isFullMode: false,
		hidden: true,
		state: 'initial',
		code: '',
		action: '',
		_askCode: false
	},
	computed: {
		'getState': function() {
			return this.state;
		},
		'isArmed': function() {
			return this.state == 'away' || this.state == 'stay';
		},
		'isDisarmed': function() {
			return this.state == 'disarmed';
		},
		'statusMessage': function() {
			if (this.isDisarmed) {
				return '<i class="fa fa-unlock fa-3x"></i> Currently disarmed';
			}
			if (this.isArmed) {
				return '<i class="fa fa-lock fa-3x locked"></i> ARMED';
			}
			return 'Determining status ...';
		},
		'askCode': {
			get: function() {
				return this._askCode;
			},
			set: function(val) {
				this._askCode = val;
				if (val) {
					this.code     = '';
					$('#keypadmodal').modal('show');
				} else {
					$('#keypadmodal').modal('hide');
				}
			}
		}
	},
	methods: {
		'performAction': function(action) {
			this.action = action;
			if (this.code.length != 4) {
				//console.log('ask code is true');
				this.askCode = true;
				return;
			}
			this.processAction();
		},
		'setCode': function(e) {
			this.code += e.currentTarget.value;
			if (this.code.length >= 4) {
				this.askCode = false;
				this.processAction();
			}
		},
		'processAction': function() {
			var bufcopy = this.code;
			var burl    = $('body').data('base-url');

			if (this.action == 'disarm') {
				bufcopy += '1';
			}
			if (this.action == 'stay') {
				bufcopy += '2';
			}
			if (this.action == 'away') {
				bufcopy += '3';
			}

			var beepcount = bufcopy.length;
			this.reset();
			$.ajax(burl+'kp/main/send/?k='+encodeURIComponent(bufcopy))
			.done( function(data) {
	//			console.log(data);
			});

		},
		'reset': function() {
			this.code   = '';
			this.state  = 'initial';
			this.action = '';

		}
	}
});

wsDisplay2(app);

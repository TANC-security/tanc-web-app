
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
				return '<i class="fa fa-unlock fa-3x"></i><br/>Currently disarmed';
			}
			if (this.isArmed) {
				return '<i class="fa fa-lock fa-3x locked"></i><br/>ARMED';
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
	watch: {
		state: function(newval, oldval) {
			var body = document.getElementsByTagName("BODY")[0];
			if (newval == 'away' || newval == 'stay') {
				body.classList.remove('disarmed');
				body.classList.add('armed');
			} else if (newval != '') {
				body.classList.remove('disarmed');
				body.classList.remove('armed');
			}
			if(newval == 'disarmed') {
				body.classList.add('disarmed');
				body.classList.remove('armed');
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

socketserver.on('display', function(packet) {
	app.state = packet.armed;
});

socketserver.on('debug', function(packet) {
	console.log(packet);
});


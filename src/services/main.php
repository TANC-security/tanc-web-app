<?php

class Services_Main {


	public function mainAction($request, $response) {
		$serviceList = $this->getDefinedServices();
		$this->injectStatus($serviceList);
		$response->serviceList = $serviceList;

		if (!$this->testForSystemd()) {
			$response->addUserMessage('This device does not have systemd available.  Cannot restart services.', 'warn');
		}


		$response->addTo('extraJs', $this->getPageJs());
	}

	public function refreshAction($request, $response) {
		$svc = escapeshellarg($_GET['svc']);
		
		$output = [];
		$ret    = '';
		exec('sudo /bin/systemctl restart '.$svc, $output, $ret);
		if ($ret) {
			$response->statusCode = 500;
			$response->retval = $ret;
			$response->svc = $svc;
			return;
		}
		$output = [];
		$ret    = '';
		exec('sudo /bin/systemctl is-active '.$svc, $output, $ret);
		$response->status = implode(' ',$output);
	}

	public function testForSystemd() {
		$output = [];
		$ret    = '';
		exec('systemctl', $output, $ret);
		if ($ret == 127) {
			return FALSE;
		}
		return TRUE;
	}

	public function injectStatus(&$serviceList) {
		foreach ($serviceList as &$_svc) {
			$n = $_svc['name'];
			$output = [];
			$ret    = '';
			exec('systemctl is-active '.$n, $output, $ret);
			if ($ret) {
				$_svc['status'] = 'n/a';
				continue;
			}
			$_svc['status'] = implode(' ',$output);
		}
	}

	public function getDefinedServices() {
		return [ 
			['name'=>'tanc-ws-display'],
			['name'=>'tanc-queue-reader'],
			['name'=>'tanc-serial-comm']
		];
	}

	public function getPageJs() {
return <<< EOF
		<script type="text/javascript">
$(document).ready(function() {

	var burl = $('body').data('base-url');
	var kpbuf = '';
	var timeoutRef;
	var to = 600;
	var doneTyping = function() {
		
		if (!timeoutRef) return;
		if (kpbuf == '') return;
		timoutRef = null;
		var bufcopy = kpbuf;
		kpbuf = '';
		$.ajax(burl+'kp/main/send/?k='+encodeURIComponent(bufcopy));
showKpDebug(encodeURIComponent(bufcopy)) ;
	}
	

	$('.datatable__refresh').on('click',function(e) {
		var button = $(e.currentTarget);
		var svc = button.data('svc');
		if (svc == undefined) {
			return false;
		}

		button.toggleClass('fa-spin');
		$.ajax(
			burl+'services/?action=refresh&svc='+encodeURIComponent(svc)
		).success(function(data, stats, hxr) {
			//update the table's status column
			$('.datatable__statuscol[data-svc='+svc+']').innerHTML = data.status;
		}).complete(function(xhr, stats) {
			button.toggleClass('fa-spin');
		});
	});

});
		</script>
EOF;
	}



}

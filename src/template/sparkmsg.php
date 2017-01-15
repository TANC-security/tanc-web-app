<?php

class Template_Sparkmsg {

	public function template($response, $template_section) {
		$msgList = $response->sparkmsg;
		if (!is_array($msgList)) {
			return;
		}

		foreach ($msgList as $_idx => $_msg) {
			$cssType = $_msg['type'];

			switch ($_msg['type']) {
				case 'msg_warn':
				case 'warning':
				case 'warn':
					$cssType = 'warning';
					break;
				case 'msg_error':
				case 'error':
				case 'err':
					$cssType = 'danger';
					break;
			}
			echo'
<div class="alert alert-'.$cssType.' alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
';
		echo $_msg['msg'];
echo '
</div>
';
			unset($msgList[$_idx]);
		}
		$response->set('sparkmsg',$msgList);
	}
}

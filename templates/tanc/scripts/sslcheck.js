$(document).ready(function(){
/*
 * <a href="/tanc-webapp/templates/selfsignwithus_root_certificate.crt">Download cert</a>
 * */

	var logger = function() {
		console.log(store.getState());
	}

	//$_ is prefix for visual components;
	var $_notice;
	var $_diag;

	var burl = $('body').data('base-url');
	var https = location.href.substr(0, 5);
	if (https == 'https') {
		return;
	}
	if (https.indexOf('10.10.10.10') > 0) {
		return;
	}
	showSSLFailedNotice('Your connection is not secure, it is advisable to enable SSL by clicking <a href="#" class="create-ssl" style="font-weight:bold;text-decoration:underline;">here</a>.');

	function hideDialog() {
		if ($_diag) {
			$_diag.modal('hide');
//			$_diag.hide();
//			$_diag.remove();
		}
	}
	function showDialog(message, props) {
		if ($_diag) {
			hideDialog();
		}
		var props = props || {'closeButton': false, 'spinner':true}
		var spinner = '';
		if ( props.spinner ) {
			spinner = '<i class=\"fa fa-spin fa-spinner\"></i> ';
		}
		$_diag = bootbox.dialog({
			message: "<div class=\"text-center\">"+spinner+message+" ...</div>",
			closeButton: props.closeButton,
		});
	}

	function showSSLFailedNotice(message) {
		if ($_notice) { $_notice.remove(); } //return;
		$_notice = new PNotify({ 
			title: 'SSL Warning',
			text: message,
			type: 'error',
			pause: false,
			hide: false,
			styling: 'bootstrap3'
		});
	}
	function hideSSLFailedNotice() {
		if (!$_notice) return;
		$_notice.remove();
	}

	$('body').on('click', '.create-ssl', function(evt) {
		//try to load cross-origin https
		//if fails (jsonp doesn't fail with CORS)
		//then start to gen a cert, otherwise redirect
		sburl = burl.replace('http:', 'https:');
		$.ajax(sburl +'main/sslcheck/ping', {
			dataType: "jsonp",
			crossDomain: true,
			jsonpCallback: 'sslcheck'
		}).fail(function(xhr, textStatus, error) {
			hideSSLFailedNotice()
			hideSSLFailedNotice( );
			//gen new root cert
			// create modal
			showDialog("Setting up SSL");

			window.setTimeout(
				genCert, 1500
			);
		}).done(function(data, textStatus, xhr) {
			window.location = sburl;
		});
	});

	function genCert() {
		$.post(burl+'main/sslcheck/', {
			'action': 'gencert'
		}).done(function(xhr, textStatus, error) {
			//display cert for download
			var burls = 'https' + burl.substr(4);
			console.log(burl);
			showDialog(
				'<h3>Done</h3><ol><li>Save your certificate <a href="'+burl+'main/sslcheck/dlroot">here</a></li><li>Import the certificate into your browser.</li><li>Continue on to the secure page by clicking <a href="'+burls+'">here</a>.</li></ol>',
				{'spinner': false}
			);

		}).fail(function(data, textStatus, xhr) {
			msg = data['user-message'] || null;
			store.dispatch({'type':'SSL_ERROR', 'text':msg});
		});
	}

function doModal(heading, formContent) {
    html =  '<div id="dynamicModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="confirm-modal" aria-hidden="true">';
    html += '<div class="modal-dialog">';
    html += '<div class="modal-content">';
    html += '<div class="modal-header">';
    html += '<a class="close" data-dismiss="modal">×</a>';
    html += '<h4>'+heading+'</h4>'
    html += '</div>';
    html += '<div class="modal-body">';
    html += formContent;
    html += '</div>';
    html += '<div class="modal-footer">';
    html += '<span class="btn btn-primary" data-dismiss="modal">Close</span>';
    html += '</div>';  // content
    html += '</div>';  // dialog
    html += '</div>';  // footer
    html += '</div>';  // modalWindow
    $('body').append(html);
    $("#dynamicModal").modal();
    $("#dynamicModal").modal('show');

    $('#dynamicModal').on('hidden.bs.modal', function (e) {
        $(this).remove();
    });
}
});


if (typeof Object.assign != 'function') {
  Object.assign = function(target, varArgs) { // .length of function is 2
    'use strict';
    if (target == null) { // TypeError if undefined or null
      throw new TypeError('Cannot convert undefined or null to object');
    }

    var to = Object(target);

    for (var index = 1; index < arguments.length; index++) {
      var nextSource = arguments[index];

      if (nextSource != null) { // Skip over if undefined or null
        for (var nextKey in nextSource) {
          // Avoid bugs when hasOwnProperty is shadowed
          if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
            to[nextKey] = nextSource[nextKey];
          }
        }
      }
    }
    return to;
  };
}


<div id="wait" class="well">
Loading Wifi Access Points...
</div>
<script id="page-template" type="text/x-handlebars-template">
\{{^wifiApList}} 
<div class="alert alert-danger">
No wifi access points found :(
</div>
\{{/wifiApList}} 

\{{#wifiApList}} 
<div class="well text-center col-md-6 col-xs-12 wifiap \{{#if connected}}connected\{{/if}}" data-address="\{{address}}" data-ssid="\{{ssid}}">
<div class="icon-connected collapse"><i class="fa fa-check"></i></div>
<h2><i class="fa fa-wifi fa-4x"></i><br/>\{{ssid}}\{{#unless ssid}}<i>no name</i>\{{/unless}}</h2>
Strength: \{{quality}}%
<br/>
<span style="font-size:70%">Address: \{{address}}</span>
</div>
\{{/wifiApList}} 
</script>


<div class="modal fade" tabindex="-1" role="dialog" id="modal-wifi-setup">
  <div class="modal-dialog">
	<form data-async method="POST" data-dialog="#modal-wifi-setup" action={{updateUrl}}>
    <div class="modal-content">
      <div class="modal-header">
      </div>
      <div class="modal-body">

		Enter the Password for this WIFI Access Point.
		<br/>
		<input type="text"   name="psk"     value="">
        <input type="hidden" name="ssid"    value="">
        <input type="hidden" name="address" value="">

      </div>
      <div class="modal-footer">
		<input type="button" value="Cancel" data-dismiss="modal" class="btn btn-default">
		<input type="submit" value="Use this AP" class="btn btn-success">
      </div>
    </div><!-- /.modal-content -->
	</form>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

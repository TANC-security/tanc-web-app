<style type="text/css">
.icon-connected{
position:absolute;
right:0;
margin-right:2em;
font-size:200%;
color:#3E3;
}
.wifiap.connected > .icon-connected {
display:block !important;
}
</style>
{{#wifiApList}} 
<div class="well text-center col-md-12 col-xs-12 wifiap {{#if connected}}connected{{/if}}" data-address="{{address}}">
<div class="icon-connected collapse"><i class="fa fa-check"></i></div>
<h2><i class="fa fa-wifi fa-4x"></i><br/>{{ssid}}</h2>
Strength: {{quality}}%
<br/>
<span style="font-size:70%">Address: {{address}}</span>
</div>
{{/wifiApList}} 

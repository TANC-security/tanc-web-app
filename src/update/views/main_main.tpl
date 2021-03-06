{{#if updates}}
<h2>Pending Updates Found!</h2>
<table class="table table-striped">
<tbody>
{{#updates}}
<tr><td>
<label>
{{.}}
</label>
</td>
<td>
<button type="button" class="btn btn-default btn-install" value="{{.}}">Update</button>
</td>
</tr>
{{/updates}}
</tbody>
</table>
{{else}}
<div class="alert alert-info">
<h2>All software is update to date</h2>
</div>
{{/if}}


<h2>All Available Packages</h2>
<p>If you need to install a new package, or force a re-install, use the following buttons.</p>
<table class="table table-striped">
<tbody>
{{#packages}}
<tr><td>
<label>
{{.}}
</label>
</td>
<td>
<button type="button" class="btn btn-default btn-install" value="{{.}}">Install or Update</button>
</td>
</tr>
{{/packages}}
</tbody>
</table>

<h2>Check for updates</h2>
<p>Force an update check now.</p>
<form method="POST">
<button type="submit" class="btn btn-default">check now &hellip;</button>
<input type="hidden" name="action" value="update">
</form>

<table border="1" width="100%" class="datatable">
<thead>
	<tr>
		<th>Name</th>
		<th>State</th>
		<th>Reload</th>
	</tr>
</thead>

<tbody>
{{#each serviceList}}
	<tr>
		<td>{{name}}</td>
		<td class="datatable__statuscol" data-svc="{{name}}">{{status}}</td>
		<td><button class="btn btn-default datatable__refresh" data-svc="{{name}}"><i class="fa fa-refresh"></i></button></td>
	</tr>
{{/each}}
</tbody>
</table>

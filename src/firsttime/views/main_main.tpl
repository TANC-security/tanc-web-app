<div class="col-md-6 col-md-offset-3">
<h2>Create Admin Account</h2>
<p>There doesn't appear to be any other accounts on this system.  If this is a brand new system, continue with creating your first admin account.</p>

{{{Template_Zendform::formOpenTag form}}}
{{#each form}}
<div class="input-group">
{{{Template_Zendform::rowHelper . class="form-control"}}}
</div>
{{/each}}
</form>

</div>

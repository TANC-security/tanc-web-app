<div class="row">
<div class="col-sm-6">
<h2>SMTP Settings</h2>
{{{Template_Zendform::formOpenTag smtpForm}}}
{{#each smtpForm}}
<div class="input-group">
{{{Template_Zendform::rowHelper . class="form-control"}}}
</div>
{{/each}}
</form>
<span class="note">If you want to send from your Gmail account, please allow "less secure apps" access via the following link:
<a target="_blank" href="https://www.google.com/settings/security/lesssecureapps">https://www.google.com/settings/security/lesssecureapps</a>
</div>

<div class="col-sm-6">
<h2>Email Notification Emails</h2>
{{{Template_Zendform::formOpenTag emailForm}}}
{{#each emailForm}}
<div class="input-group">
{{{Template_Zendform::rowHelper . class="form-control"}}}
</div>
{{/each}}
</form>
</div>
</div>

<div class="row">

<h2>Test ARM event</h2>
<form method="POST">
<button type="submit">Test Arm</button>
<input type="hidden" value="test" name="action"/>
</form>
</div>

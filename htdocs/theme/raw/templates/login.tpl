{include file="header.tpl"}

<div class="row">
<div class="col-md-3 login-card">
	<div class="card">
	    <h3 class="card-header">
	        {str tag="login"}
	        <span class="float-right">{contextualhelp plugintype='core' pluginname='core' section='loginbox'}</span>
	    </h3>
	    <div class="card-body">
	        <noscript><p>{str tag="javascriptnotenabled"}</p></noscript>
	        {dynamic}{$messages.loginbox|safe}{/dynamic}
	        <div id="loginform_container">
	            {$login_form|safe}
	        </div>
	    </div>
	</div>
</div>
</div>

{include file="footer.tpl"}

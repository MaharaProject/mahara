{include file="header.tpl"}

<div class="row">
<div class="col-md-3 login-panel">
	<div class="panel panel-default">
	    <h3 class="panel-heading">
	        {str tag="login"}
	        <span class="pull-right">{contextualhelp plugintype='core' pluginname='core' section='loginbox'}</span>
	    </h3>
	    <div class="panel-body">
	        <noscript><p>{str tag="javascriptnotenabled"}</p></noscript>
	        {dynamic}{insert_messages placement='loginbox'}{/dynamic}
	        <div id="loginform_container">
	            {$login_form|safe}
	        </div>
	    </div>
	</div>
</div>
</div>

{include file="footer.tpl"}

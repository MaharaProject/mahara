{include file='header.tpl'}
<div class="lead">
    {str tag="testclientinstructions" section="auth.webservice"}
</div>
{if $disabled}
    <div class="error alert alert-warning">{str tag=wsauthnotenabled section='auth.webservice'}</div>
{elseif $disabledhttps}
    <div class="error alert alert-warning">{str tag=unabletoruntestclient section='auth.webservice'}</div>
{elseif $disabledprotocols}
    <div class="error alert alert-warning">{str tag=nowsprotocolsenabled section='auth.webservice' arg1=$disabledprotocols}</div>
{else}
    <div class="card card-body">
    {$form|safe}
    </div>
{/if}

{include file='footer.tpl'}
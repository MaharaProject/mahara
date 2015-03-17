{include file='header.tpl'}
<div><b>{str tag="testclientinstructions" section="auth.webservice"}</b></div>
{if $disabled}
    <div class="error">{str tag=wsauthnotenabled section='auth.webservice'}</div>
{elseif $disabledprotocols}
    <div class="error">{str tag=nowsprotocolsenabled section='auth.webservice' arg1=$disabledprotocols}</div>
{else}
    {$form|safe}
{/if}

{include file='footer.tpl'}
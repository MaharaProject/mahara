{include file='header.tpl'}
<div><b>{str tag="testclientinstructions" section="auth.webservice"}</b></div>
{if $disabled}
    <div class="error">{str tag=wsauthnotenabled section='auth.webservice'}</div>
{else}
    {$form|safe}
{/if}

{include file='footer.tpl'}
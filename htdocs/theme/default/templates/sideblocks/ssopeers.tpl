    <h3>{str tag="ssopeers"}{contextualhelp plugintype='core' pluginname='core' section='ssopeers'}</h3>
{if $data}
    <ul id="sitemenu">
{foreach from=$data item=peer}
    	<li class="{cycle values=r0,r1}"><a href="/auth/xmlrpc/jump.php?wr={$peer.wwwroot|escape}&ins={$peer.instance|escape}">{$peer.name}</a></li>
{/foreach}
    </ul>
{/if}

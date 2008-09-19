    <h3>{str tag="ssopeers"}{contextualhelp plugintype='core' pluginname='core' section='ssopeers'}</h3>
{if $data}
    <ul id="sitemenu">
{foreach from=$data item=peer}
{if $peer.instance != $userauthinstance}
        <li><a href="{$WWWROOT}auth/xmlrpc/jump.php?wr={$peer.wwwroot|escape}&ins={$peer.instance|escape}">{$peer.name|escape}</a></li>
{else}
        <li><a href="{$peer.wwwroot|escape}">{$peer.name|escape}</a></li>
{/if}
{/foreach}
    </ul>
{/if}

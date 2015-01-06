    <div class="sidebar-header"><h3>{str tag="networkservers" section="auth.xmlrpc"}{contextualhelp plugintype='auth' pluginname='xmlrpc' section='networkservers'}</h3></div>
    <div class="sidebar-content">
{if $sbdata}
    <ul id="sitemenu">
{foreach from=$sbdata item=peer}
{if $peer.instance != $userauthinstance}
{if !$MNETUSER}
        <li><a href="{$WWWROOT}auth/xmlrpc/jump.php?wr={$peer.wwwroot}&amp;ins={$peer.instance}">{$peer.name}</a></li>
{/if}
{else}
        <li><a href="{$peer.wwwroot}">{$peer.name}</a></li>
{/if}
{/foreach}
    </ul>
{/if}
</div>


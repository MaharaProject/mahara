{if $sbdata}
<div class="panel panel-default">
    <h3 class="panel-heading">
        {str tag="networkservers" section="auth.xmlrpc"}
        <span class="pull-right">
            {contextualhelp plugintype='auth' pluginname='xmlrpc' section='networkservers'}
        </span>
    </h3>
    <ul id="sitemenu" class="list-group">
        {foreach from=$sbdata item=peer}
        {if $peer.instance != $userauthinstance}
        {if !$MNETUSER}
                <li class="list-group-item"><a href="{$WWWROOT}auth/xmlrpc/jump.php?wr={$peer.wwwroot}&amp;ins={$peer.instance}">{$peer.name}</a></li>
        {/if}
        {else}
                <li class="list-group-item"><a href="{$peer.wwwroot}">{$peer.name}</a></li>
        {/if}
        {/foreach}
    </ul>
</div>
{/if}
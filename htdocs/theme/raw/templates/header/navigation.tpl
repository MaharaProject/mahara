

{if $MAINNAV}
        <div id="main-nav">
            <ul>{strip}
{foreach from=$MAINNAV item=item}
                <li{if $item.selected} class="selected"{/if}><span><a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if} class="{if $item.path}{$item.path}{else}dashboard{/if}">{$item.title}</a></span></li>
{/foreach}
{if $ADMIN || $INSTITUTIONALADMIN}
                <li><span><a href="{$WWWROOT}" accesskey="h" class="return-site">{str tag="returntosite"}</a></span></li>
{elseif $USER->get('admin')}
                <li><span><a href="{$WWWROOT}admin/" accesskey="a" class="admin-site">{str tag="siteadministration"}</a></span></li>
{elseif $USER->is_institutional_admin()}
                <li><span><a href="{$WWWROOT}admin/users/search.php" accesskey="a" class="admin-user">{str tag="institutionadministration"}</a></span></li>
{/if}
            {/strip}</ul>
            
        </div>

        <div id="sub-nav">
{if $SELECTEDSUBNAV}
            <ul>{strip}
{foreach from=$SELECTEDSUBNAV item=item}
                <li{if $item.selected} class="selected"{/if}><a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title}</a></li>
{/foreach}
            {/strip}</ul>
{/if}
        </div>
{/if}

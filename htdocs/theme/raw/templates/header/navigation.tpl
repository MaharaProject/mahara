

{if $MAINNAV}
        <div id="main-nav">
            <ul>{strip}
{foreach from=$MAINNAV item=item}
                <li{if $item.selected} class="selected"{/if}><a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title}</a></li>
{/foreach}
{if $ADMIN || $INSTITUTIONALADMIN}
                <li><a href="{$WWWROOT}" accesskey="h">{str tag="returntosite"}</a></li>
{elseif $USER->get('admin')}
                <li><a href="{$WWWROOT}admin/" accesskey="a">{str tag="siteadministration"}</a></li>
{elseif $USER->is_institutional_admin()}
                <li><a href="{$WWWROOT}admin/users/search.php" accesskey="a">{str tag="institutionadministration"}</a></li>
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

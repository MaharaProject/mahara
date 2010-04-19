{if $MAINNAV}
        <div id="main-nav">
            <ul>{strip}
{foreach from=$MAINNAV item=item}
                <li{if $item.selected}{assign var=MAINNAVSELECTED value=$item} class="selected"{/if}><a href="{if get_config('httpswwwroot') && $item.url=='account/'}{$HTTPSWWWROOT}{else}{$WWWROOT}{/if}{$item.url|escape}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title|escape}</a></li>
{/foreach}
{if $LOGGEDIN}{if $USER->get('admin') || $USER->is_institutional_admin()}
{if $ADMIN || $INSTITUTIONALADMIN}
                <li><a href="{$WWWROOT}" accesskey="h">{str tag="returntosite"}</a></li>
{elseif $USER->get('admin')}
                <li><a href="{$WWWROOT}admin/" accesskey="a">{str tag="siteadministration"}</a></li>
{else}
                <li><a href="{$WWWROOT}admin/users/search.php" accesskey="a">{str tag="useradministration"}</a></li>
{/if}
{/if}
                <li><a href="{$WWWROOT}?logout" accesskey="l">{str tag="logout"}</a></li>
{/if}
            {/strip}</ul>
        </div>
        <div id="sub-nav">
{if $MAINNAVSELECTED.submenu}
            <ul>{strip}
{foreach from=$MAINNAVSELECTED.submenu item=item}
                <li{if $item.selected} class="selected"{/if}><a href="{if get_config('httpswwwroot') && $item.url=='account/'}{$HTTPSWWWROOT}{else}{$WWWROOT}{/if}{$item.url|escape}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title|escape}</a></li>
{/foreach}
            {/strip}</ul>
{/if}
        </div>
{/if}

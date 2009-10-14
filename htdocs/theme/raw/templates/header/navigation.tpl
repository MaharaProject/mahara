{if $MAINNAV}
        <div id="main-nav">
            <ul>{strip}
{foreach from=$MAINNAV item=item}
                <li{if $item.selected}{assign var=MAINNAVSELECTED value=$item} class="selected"{/if}><a href="{if get_config('httpswwwroot') && $item.url=='account/'}{$HTTPSWWWROOT}{else}{$WWWROOT}{/if}{$item.url|escape}">{$item.title|escape}</a></li>
{/foreach}
{if $LOGGEDIN}{if $USER->get('admin') || $USER->is_institutional_admin()}
{if $ADMIN || $INSTITUTIONALADMIN}
                <li><a href="{$WWWROOT}">{str tag="returntosite"}</a></li>
{elseif $USER->get('admin')}
                <li><a href="{$WWWROOT}admin/">{str tag="siteadministration"}</a></li>
{else}
                <li><a href="{$WWWROOT}admin/users/search.php">{str tag="useradministration"}</a></li>
{/if}
{/if}
                <li><a href="{$WWWROOT}?logout">{str tag="logout"}</a></li>
{/if}
            {/strip}</ul>
        </div>
        <div id="sub-nav">
{if $MAINNAVSELECTED.submenu}
            <ul>{strip}
{foreach from=$MAINNAVSELECTED.submenu item=item}
                <li{if $item.selected} class="selected"{/if}><a href="{if get_config('httpswwwroot') && $item.url=='account/'}{$HTTPSWWWROOT}{else}{$WWWROOT}{/if}{$item.url|escape}">{$item.title|escape}</a></li>
{/foreach}
            {/strip}</ul>
{/if}
        </div>
{/if}

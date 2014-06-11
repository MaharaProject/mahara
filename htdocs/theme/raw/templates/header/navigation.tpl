

{if $MAINNAV}
        <div id="main-nav" class="{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}{if $DROPDOWNMENU}dropdown-adminnav {else}adminnav {/if}{/if}main-nav">
            <ul id="{if $DROPDOWNMENU}dropdown-nav{else}nav{/if}">
{strip}
{foreach from=$MAINNAV item=item}
                <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} selected{/if}{if $DROPDOWNMENU} dropdown-nav-home{/if}"><span><a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if} class="{if $item.path}{$item.path}{else}dashboard{/if}">{if $item.accessibletitle && !$DROPDOWNMENU}<span aria-hidden="true" role="presentation">{/if}{$item.title}{if $item.accessibletitle && !$DROPDOWNMENU}</span> <span class="accessible-hidden">({$item.accessibletitle})</span>{/if}{if $DROPDOWNMENU && $item.submenu} <span class="accessible-hidden">({str tag=dropdownmenu})</span>{/if}</a></span>
{if $DROPDOWNMENU}{if $item.submenu}
                    <ul class="dropdown-sub">
{strip}
{foreach from=$item.submenu item=subitem}
                        <li{if $subitem.selected} class="selected"{/if}><span>
                            <a href="{$WWWROOT}{$subitem.url}"{if $subitem.accesskey} accesskey="{$subitem.accesskey}"{/if}>{$subitem.title}</a>
                        </span></li>
{/foreach}
{/strip}
                        <div class="cl"></div>
                    </ul>
{/if}{/if}
                </li>
{/foreach}
{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}
                <li class="returntosite"><span><a href="{$WWWROOT}" accesskey="h" class="return-site">{str tag="returntosite"}</a></span></li>
{elseif $USER->get('admin')}
                <li class="siteadmin"><span><a href="{$WWWROOT}admin/" accesskey="a" class="admin-site">{str tag="administration"}</a></span></li>
{elseif $USER->is_institutional_admin()}
                <li class="instituteadmin"><span><a href="{$WWWROOT}admin/users/search.php" accesskey="a" class="admin-user">{str tag="administration"}</a></span></li>
{elseif $USER->get('staff')}
                <li class="siteinfo"><span><a href="{$WWWROOT}admin/users/search.php" accesskey="a" class="admin-user">{str tag="siteinformation"}</a></span></li>
{elseif $USER->is_institutional_staff()}
                <li class="instituteinfo"><span><a href="{$WWWROOT}admin/users/search.php" accesskey="a" class="admin-user">{str tag="institutioninformation"}</a></span></li>
{/if}
            {/strip}</ul>

        </div>
{if $DROPDOWNMENU}
{else}
        <div id="sub-nav">
{if $SELECTEDSUBNAV}
            <ul>
{strip}
{foreach from=$SELECTEDSUBNAV item=item}
                <li{if $item.selected} class="selected"{/if}><span><a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title}</a></span></li>
{/foreach}
            {/strip}</ul>
{/if}
            <div class="cb"></div>
        </div>
{/if}
{/if}

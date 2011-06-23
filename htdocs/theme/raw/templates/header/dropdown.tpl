{if $MAINNAV}
      <div id="main-nav" class="dropdown-main">
            <ul id="dropdown-nav">
{strip}
{foreach from=$MAINNAV item=item}
{if $item.weight == 10}
                <li class="{if $item.selected}selected{/if} dropdown-nav-home"{if $item.accesskey} id="{$item.accesskey}"{/if}>
                    <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title}</a>
{else}
                <li{if $item.accesskey} id="{$item.accesskey}"{/if} {if $item.selected} class="selected"{/if} >
                    <a href="{if $INSTITUTIONALADMIN}{$WWWROOT}{$item.url}{else}#{/if}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title}</a>
{/if}
{if $item.submenu}
                    <ul class="dropdown-sub">
{strip}
{foreach from=$item.submenu item=subitem}
                        <li>
                            <a href="{$WWWROOT}{$subitem.url}"{if $subitem.accesskey} accesskey="{$subitem.accesskey}"{/if}>{$subitem.title}</a>
                        </li>
{/foreach}
{/strip}
                    </ul>
{/if}
                </li>
{/foreach}
{if $ADMIN || $INSTITUTIONALADMIN}
                <li><a href="{$WWWROOT}" accesskey="h">{str tag="returntosite"}</a></li>
{elseif $USER->get('admin')}
                <li><a href="{$WWWROOT}admin/" accesskey="a">{str tag="siteadministration"}</a></li>
{elseif $USER->is_institutional_admin()}
                <li><a href="{$WWWROOT}admin/users/search.php" accesskey="a">{str tag="institutionadministration"}</a></li>
{/if}
{/strip}
            </ul>
      </div>
 {/if}

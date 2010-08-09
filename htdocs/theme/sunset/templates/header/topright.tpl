<div id="header-right">
{if $RIGHTNAV}
        <div id="right-nav">
            <ul>{strip}
{foreach from=$RIGHTNAV item=item}
                <li{if $item.selected}{assign var=MAINNAVSELECTED value=$item} class="selected"{/if}><a href="{if $item.wwwroot}{$item.wwwroot}{else}{$WWWROOT}{/if}{$item.url}">{if $item.title}{$item.title}{/if}{if $item.icon}<img src="{$item.icon}" alt="{$item.alt}">{if isset($item.count)}<span class="navcount{if $item.countclass} {$item.countclass}{/if}">{$item.count}</span>{/if}</a></li>
{/foreach}
                <li class="btn-logout"><a href="{$WWWROOT}?logout" accesskey="l">{str tag="logout"}</a></li>
            {/strip}</ul>
        </div>
{/if}

{if !$nosearch && !$LOGGEDIN && (count($LANGUAGES) > 1)}
        <form id="language-select" method="post" action="">
            <div>
                <label>{str tag=language}: </label>
                <select name="lang">
                    <option value="default" selected="selected">{$sitedefaultlang}</option>
{foreach from=$LANGUAGES key=k item=i}
                    <option value="{$k}">{$i}</option>
{/foreach}
                </select>
                <input type="submit" class="submit" name="changelang" value="{str tag=change}">
            </div>
        </form>
{/if}

{if !$nosearch && $LOGGEDIN}        {user_search_form}{/if}
</div>


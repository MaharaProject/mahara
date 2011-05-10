<div id="header-right">
        <div id="right-nav">
            <ul>{strip}
{if $RIGHTNAV}
{foreach from=$RIGHTNAV item=item}
                <li{if $item.selected}{assign var=MAINNAVSELECTED value=$item} class="selected"{/if}><a href="{if $item.wwwroot}{$item.wwwroot}{else}{$WWWROOT}{/if}{$item.url}">{if $item.title}{$item.title}{/if}{if $item.icon}<img src="{$item.icon}" alt="{$item.alt}">{if isset($item.count)}<span class="navcount{if $item.countclass} {$item.countclass}{/if}">{$item.count}</span>{/if}</a> | </li>
{/foreach}
                <li class="btn-logout"><a href="{$WWWROOT}?logout" accesskey="l">{str tag="logout"}</a></li>
{/if}
            {/strip}
{if !$nosearch && !$LOGGEDIN && (count($LANGUAGES) > 1)}
            	<li>
        			<form id="language-select" method="post" action="">
                		<label>{str tag=language}: </label>
                		<select name="lang">
                    	<option value="default" selected="selected">{$sitedefaultlang}</option>
{foreach from=$LANGUAGES key=k item=i}
                   			<option value="{$k}">{$i}</option>
{/foreach}
                			</select>
                		<input type="submit" class="submit" name="changelang" value="{str tag=change}">
        			</form>
            	</li>
{/if}
{if !$LOGGEDIN && !$SIDEBARS && !$LOGINPAGE}        <li><a href="{$WWWROOT}?login" accesskey="l">{str tag="login"}</a></li> {/if}
			</ul>
        </div>


{if !$nosearch && $LOGGEDIN}        {user_search_form}{/if}

</div>


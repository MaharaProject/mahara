<div id="header-right">
        <div id="right-nav">
            <ul>{strip}
{if $LOGGEDIN}      <li class="identity"><a href="{profile_url($USER)}">{$USER|display_default_name}</a></li>{/if}
{if $RIGHTNAV}
{foreach from=$RIGHTNAV item=item}
                <li class="{$item.path}{if $item.selected}{assign var=MAINNAVSELECTED value=$item} selected{/if}"> | <a href="{if $item.wwwroot}{$item.wwwroot}{else}{$WWWROOT}{/if}{$item.url}">{if $item.title}{$item.title}{/if}{if $item.icon}<img src="{$item.icon}" alt="{$item.alt}">{if isset($item.count)}<span class="navcount{if $item.countclass} {$item.countclass}{/if}">{$item.count}</span>{/if}</a></li>
{/foreach}
                <li class="btn-logout"> | <a href="{$WWWROOT}?logout" accesskey="l">{str tag="logout"}</a></li>
{/if}
            {/strip}
{if !$nosearch && !$LOGGEDIN && $languageform}
            	<li>
                    {$languageform|safe}
            	</li>
{/if}
{if !$LOGGEDIN && !$SIDEBARS && !$LOGINPAGE}        <li><a href="{$WWWROOT}?login" accesskey="l">{str tag="login"}</a></li> {/if}
			</ul>
        </div>


{if !$nosearch && $LOGGEDIN}        {user_search_form}{/if}

</div>



<ul class="nav navbar-nav navbar-right top-nav {if $languageform}with-languageform{/if}">
    {strip}
        {if $USERMASQUERADING && $LOGGEDIN}
        <li class="backto-be-admin has-icon">
            <a href="{$becomeyoulink}" title="{$becomeyouagain}">
                <span class="icon icon-undo" role="presentation"></span>
                <span class="nav-title">{$becomeyouagain}</span>
            </a>
        </li>
        {/if}
        {if $LOGGEDIN}
        <li class="identity has-icon">
            <a href="{profile_url($USER)}" class="user-icon">
                <img src="{profile_icon_url user=$USER maxheight=25 maxwidth=25}">
            </a>
            <a class="dropdown-toggle dropdown-toggle-split icon icon-chevron-down" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="sr-only">Toggle Dropdown</span>
            </a>
        {/if}
        {if $RIGHTNAV}
            <ul class="dropdown-menu">
                <li class="identity has-icon">
                  <a href="{profile_url($USER)}">
                    <span class="icon icon-user" role="presentation" aria-hidden="true"></span>
                    <span class="nav-title">{$USER|display_default_name}</span>
                  </a>
                </li>
            {foreach from=$RIGHTNAV item=item}
                <li class="{$item.path}{if $item.selected}{assign var=MAINNAVSELECTED value=$item} selected{/if}{if $item.class} {$item.class}{/if}  {if $item.iconclass}has-icon{/if} dropdown-item">
                    <a {if $item.linkid}id="{$item.linkid}"{/if} {if $item.accesskey}accesskey="{$item.accesskey}" {/if}{if $item.aria}{foreach $item.aria key=key item=value}aria-{$key}="{$value}" {/foreach}{/if}href="{if $item.wwwroot}{$item.wwwroot}{else}{$WWWROOT}{/if}{$item.url}">
                        {if $item.iconclass}
                            <span class="icon icon-{$item.iconclass}" role="presentation" aria-hidden="true"></span>
                        {/if}

                        {if isset($item.count)}
                            <span class="navcount{if $item.countclass} {$item.countclass}{/if}">
                                <span class="sr-only">{$item.title}: </span>{if isset($item.unread)} {$item.unread} {else} {$item.count} {/if}
                            </span>
                        {elseif $item.title}
                            <span class="nav-title">{$item.title}</span>
                        {/if}
                    </a>
                </li>
            {/foreach}
                <li class="btn-logout has-icon">
                    <a href="{$WWWROOT}?logout" accesskey="l">
                        <span class="icon icon-sign-out" role="presentation" aria-hidden="true"></span>
                        <span class="nav-title">{str tag="logout"}</span>
                    </a>
                </li>
            </ul>
        {/if}
        </li>
    {/strip}
    {if !$LOGGEDIN && !$SHOWLOGINBLOCK && !$LOGINPAGE}
        <li id="loginlink" class="has-icon login-link">
            <a href="{$WWWROOT}?login" accesskey="l">
                <span class="icon icon-sign-in" role="presentation" aria-hidden="true"></span>
                <span>{str tag="login"}</span>
            </a>
        </li>
    {/if}
    {if !$nosearch && !$LOGGEDIN && $languageform}
        <li id="language" class="language-form">
            {$languageform|safe}
        </li>
    {/if}
</ul>


{if !$nosearch && ($LOGGEDIN || $publicsearchallowed)}
<div class="navbar-form navbar-right collapse  navbar-collapse">
    {header_search_form}
</div>
{/if}

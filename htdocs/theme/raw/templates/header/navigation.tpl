{if $MAINNAV}
<nav aria-label="{str tag=mainmenu setction=mahara}">
  <div id="main-nav" class="{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF || $INSTITUTIONALSUPPORTADMIN}adminnav{/if} nav collapse navbar-collapse nav-main">
    <ul id="nav" class="nav navbar-nav">
        {strip}
            {foreach from=$MAINNAV item=item name=menu}
            <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} active{/if}">
                {if !$item.submenu}{* Create a link to the main page *}
                <a href="{$WWWROOT}{$item.url}" class="{if $item.path}{$item.path}{else}dashboard{/if}">
                {else}{* Otherwise, create list items as buttons to expand submenus *}
                <button class="{if $item.path}{$item.path}{else}dashboard{/if} menu-dropdown-toggle navbar-toggle{if !$item.selected} collapsed{/if}" data-bs-toggle="collapse" data-bs-parent="#nav" data-bs-target="#childmenu-{$dwoo.foreach.menu.index}" aria-expanded="false">
                {/if}
                {if $item.iconclass}
                    <span class="icon icon-{$item.iconclass}" role="presentation" aria-hidden="true"></span>
                {/if}
                {if $item.accessibletitle}
                    <span role="presentation" aria-hidden="true">
                {/if}
                {$item.title}
                {if $item.accessibletitle}
                    </span>
                    <span class="accessible-hidden visually-hidden">
                        ({$item.accessibletitle})
                    </span>
                {/if}
                {if !$item.submenu}{* Close the link tag *}
                </a>
                {else}{* Close the button tag *}
                    <span class="icon icon-chevron-down navbar-showchildren" role="presentation" aria-hidden="true"></span>
                </button>
                {/if}
                {if $item.submenu}
                <ul id="childmenu-{$dwoo.foreach.menu.index}" class="{if $item.selected} show{/if} collapse child-nav">
                {strip}
                    {foreach from=$item.submenu item=subitem}
                    <li class="{if $subitem.selected}active {/if}{if $subitem.submenu}has-sub {/if}">
                        <a href="{$WWWROOT}{$subitem.url}">
                            {$subitem.title}
                        </a>
                        {if $subitem.submenu}
                        <ul class="dropdown-tertiary">
                            {foreach from=$subitem.submenu item=tertiaryitem}
                            <li{if $tertiaryitem.selected} class="selected"{/if}>
                                <a href="{$WWWROOT}{$tertiaryitem.url}">
                                    {$tertiaryitem.title}
                                </a>
                            </li>
                            {/foreach}
                        </ul>
                        {/if}
                    </li>
                    {/foreach}
                {/strip}
                </ul>
                {/if}
            </li>
            {/foreach}
        {/strip}
    </ul>
  </div>
</nav>
<nav aria-label="{str tag=adminmenu section=mahara}">
  <div id="main-nav-admin" class="{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF || $INSTITUTIONALSUPPORTADMIN}adminnav{/if} nav collapse navbar-collapse nav-main-admin">
    <ul id="navadmin" class="nav navbar-nav">
    {strip}
        {foreach from=$MAINNAVADMIN item=item name=menu}
        <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} active{/if}">
            {if !$item.submenu}{* Create a link to the main page *}
            <a href="{$WWWROOT}{$item.url}" class="{if $item.path}{$item.path}{else}dashboard{/if}">
            {else}{* Otherwise, create list items as buttons to expand submenus *}
            <button class="{if $item.path}{$item.path}{else}dashboard{/if} menu-dropdown-toggle navbar-toggle{if !$item.selected} collapsed{/if}" data-bs-toggle="collapse" data-bs-parent="#navadmin" data-bs-target="#adminchildmenu-{$dwoo.foreach.menu.index}" aria-expanded="false">
            {/if}
            {if $item.iconclass}
                <span class="icon icon-{$item.iconclass}" role="presentation" aria-hidden="true"></span>
            {/if}
            {if $item.accessibletitle}
                <span aria-hidden="true" role="presentation">
            {/if}
            {$item.title}
            {if $item.accessibletitle}
                </span>
                <span class="accessible-hidden visually-hidden">
                    ({$item.accessibletitle})
                </span>
            {/if}
            {if !$item.submenu}{* Close the link tag *}
            </a>
            {else}{* Close the button tag *}
                <span class="icon icon-chevron-down navbar-showchildren" role="presentation" aria-hidden="true"></span>
            </button>
            {/if}
            {if $item.submenu}
            <ul id="adminchildmenu-{$dwoo.foreach.menu.index}" class="{if $item.selected} show{/if} collapse child-nav">
            {strip}
                {foreach from=$item.submenu item=subitem}
                <li class="{if $subitem.selected}active {/if}{if $subitem.submenu}has-sub {/if}">
                    <a href="{$WWWROOT}{$subitem.url}">
                        {$subitem.title}
                    </a>
                    {if $subitem.submenu}
                    <ul class="dropdown-tertiary">
                        {foreach from=$subitem.submenu item=tertiaryitem}
                        <li{if $tertiaryitem.selected} class="selected"{/if}>
                            <a href="{$WWWROOT}{$tertiaryitem.url}">
                                {$tertiaryitem.title}
                            </a>
                        </li>
                        {/foreach}
                    </ul>
                    {/if}
                </li>
                {/foreach}
            {/strip}
            </ul>
            {/if}
        </li>
        {/foreach}
    {/strip}
    </ul>
  </div>
</nav>
{/if}

{if $RIGHTNAV}
<nav aria-label="{str tag=usermenu1 section=mahara}">
  <div id="main-nav-user" class="{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF || $INSTITUTIONALSUPPORTADMIN}{/if} nav collapse navbar-collapse nav-main-user">
    <ul id="navuser" class="nav navbar-nav">
      {strip}
        {foreach from=$RIGHTNAV item=item}
        <li class="{$item.path}{if $item.selected} active{/if}{if $item.class} {$item.class}{/if}  {if $item.iconclass}has-icon{/if} dropdown-item">
            {if !$item.submenu}{*if item is a link, create a link tag *}
            <a {if $item.linkid}id="{$item.linkid}"{/if} {if $item.aria}{foreach $item.aria key=key item=value}aria-{$key}="{$value}" {/foreach}{/if}href="{if $item.wwwroot}{$item.wwwroot}{else}{$WWWROOT}{/if}{$item.url}" class="menu-dropdown-toggle">
            {else} {* otherwise, create a button *}
            <button type="button" class="navbar-toggle menu-dropdown-toggle{if !$item.selected} collapsed{/if}" data-bs-toggle="collapse" data-bs-parent="#navuser" data-bs-target="#userchildmenu-{$dwoo.foreach.menu.index}" aria-expanded="false">
            {/if}
                {if $item.iconclass}
                <span class="icon icon-{$item.iconclass}" role="presentation" aria-hidden="true"></span>
                {/if}
                <span class="nav-title">{$item.title}</span>
            {if !$item.submenu}{* Close the link tag *}
            </a>
            {else}{* Close the button tag *}
                 <span class="icon icon-chevron-down navbar-showchildren" role="presentation" aria-hidden="true"></span>
            </button>
            {/if}
            {if $item.submenu}
            <ul id="userchildmenu-{$dwoo.foreach.menu.index}" class="{if $item.selected} show{/if} collapse child-nav">
               {foreach from=$item.submenu item=subitem}
               <li class="{if $subitem.selected}active {/if}{if $subitem.submenu}has-sub {/if}">
                   <a href="{$WWWROOT}{$subitem.url}">
                       {$subitem.title}
                   </a>
                   {if $subitem.submenu}
                   <ul class="dropdown-tertiary">
                       {foreach from=$subitem.submenu item=tertiaryitem}
                       <li{if $tertiaryitem.selected} class="selected"{/if}>
                           <a href="{$WWWROOT}{$tertiaryitem.url}">
                               {$tertiaryitem.title}
                           </a>
                       </li>
                       {/foreach}
                   </ul>
                   {/if}
               </li>
               {/foreach}
            </ul>
            {/if}
        </li>
        {/foreach}
        <li class="btn-logout has-icon menu-dropdown-toggle">
            <a id="logoutbutton" href="{$WWWROOT}?logout">
                <span class="icon icon-sign-out-alt" role="presentation" aria-hidden="true"></span>
                <span class="nav-title">{str tag="logout"}</span>
            </a>
            <script>
            $('#logoutbutton').click(function(e) {
                if ($(this).hasClass('disabled')) {
                    e.preventDefault();
                    return false;
                }
                $(this).addClass('disabled');
                return true;
            });
            </script>
        </li>
        {if $USERMASQUERADING && $LOGGEDIN}
        <li class="backto-be-admin has-icon">
            <a href="{$becomeyoulink}" title="{$becomeyouagain}">
                <span class="icon icon-undo left" role="presentation"></span>
                <span class="nav-title">{$becomeyouagain}</span>
            </a>
        </li>
        {/if}
      {/strip}
    </ul>
  </div>
</nav>
{/if}

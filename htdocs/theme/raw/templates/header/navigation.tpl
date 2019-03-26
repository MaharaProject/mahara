{if $MAINNAV}

<nav>
  <div id="main-nav" class="{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}adminnav{/if} nav collapse navbar-collapse nav-main" role="tabcard">
    <ul id="nav" class="nav navbar-nav">
        {strip}
            {foreach from=$MAINNAV item=item name=menu}
            <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} active{/if}">
                <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if} class="{if $item.path}{$item.path}{else}dashboard{/if} {if $item.submenu}menu-dropdown-toggle{/if}">
                    {if $item.iconclass}
                    <span class="icon icon-{$item.iconclass}" role="presentation" aria-hidden="true"></span>
                    {/if}

                    {if $item.accessibletitle}
                    <span aria-hidden="true" role="presentation" aria-hidden="true">
                        {/if}
                        {$item.title}
                        {if $item.accessibletitle}
                    </span>
                    <span class="accessible-hidden sr-only">
                        ({$item.accessibletitle})
                    </span>
                    {/if}
                </a>
                {if $item.submenu}
                <button type="button" class="navbar-showchildren navbar-toggle {if !$item.selected}collapsed{/if}" data-toggle="collapse" data-parent="#nav" data-target="#childmenu-{$dwoo.foreach.menu.index}">
                    <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                    <span class="nav-title sr-only">{str tag="showmenu" section="mahara" arg1="$item.title"}</span>
                </button>
                {/if}
                {if $item.submenu}
                <ul id="childmenu-{$dwoo.foreach.menu.index}" class="{if $item.selected} show{/if} collapse child-nav" role="menu">
                {strip}
                    {foreach from=$item.submenu item=subitem}
                    <li class="{if $subitem.selected}active {/if}{if $subitem.submenu}has-sub {/if}">
                        <a href="{$WWWROOT}{$subitem.url}"{if $subitem.accesskey} accesskey="{$subitem.accesskey}"{/if}>
                            {$subitem.title}
                        </a>
                        {if $subitem.submenu}
                        <ul class="dropdown-tertiary">
                            {foreach from=$subitem.submenu item=tertiaryitem}
                            <li{if $tertiaryitem.selected} class="selected"{/if}>
                                <a href="{$WWWROOT}{$tertiaryitem.url}"{if $tertiaryitem.accesskey} accesskey="{$tertiaryitem.accesskey}"{/if}>
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
<nav>
  <div id="main-nav-admin" class="{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}adminnav{/if} nav collapse navbar-collapse nav-main-admin" role="tabcard">
    <ul id="navadmin" class="nav navbar-nav">
    {strip}
        {foreach from=$MAINNAVADMIN item=item name=menu}
        <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} active{/if}">
            <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if} class="{if $item.path}{$item.path}{else}dashboard{/if} {if $item.submenu}menu-dropdown-toggle{/if}">
                {if $item.iconclass}
                <span class="icon icon-{$item.iconclass}" role="presentation" aria-hidden="true"></span>
                {/if}

                {if $item.accessibletitle}
                <span aria-hidden="true" role="presentation">
                    {/if}
                    {$item.title}
                    {if $item.accessibletitle}
                </span>
                <span class="accessible-hidden sr-only">
                ({$item.accessibletitle})
                </span>
                {/if}
            </a>
            {if $item.submenu}
            <button type="button" class="navbar-showchildren navbar-toggle {if !$item.selected}collapsed{/if}" data-toggle="collapse" data-parent="#navadmin" data-target="#adminchildmenu-{$dwoo.foreach.menu.index}">
                <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                <span class="nav-title sr-only">{str tag="showmenu" section="mahara" arg1="$item.title"}</span>
            </button>
            {/if}
            {if $item.submenu}
            <ul id="adminchildmenu-{$dwoo.foreach.menu.index}" class="{if $item.selected} show{/if} collapse child-nav" role="menu">
            {strip}
                {foreach from=$item.submenu item=subitem}
                <li class="{if $subitem.selected}active {/if}{if $subitem.submenu}has-sub {/if}">
                    <a href="{$WWWROOT}{$subitem.url}"{if $subitem.accesskey} accesskey="{$subitem.accesskey}"{/if}>
                        {$subitem.title}
                    </a>
                    {if $subitem.submenu}
                    <ul class="dropdown-tertiary">
                        {foreach from=$subitem.submenu item=tertiaryitem}
                        <li{if $tertiaryitem.selected} class="selected"{/if}>
                            <a href="{$WWWROOT}{$tertiaryitem.url}"{if $tertiaryitem.accesskey} accesskey="{$tertiaryitem.accesskey}"{/if}>
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
<nav>
  <div id="main-nav-user" class="{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}{/if} nav collapse navbar-collapse nav-main-user" role="tabcard">
    <ul id="navuser" class="nav navbar-nav">
      {strip}
        {foreach from=$RIGHTNAV item=item}
        <li class="{$item.path}{if $item.selected} active{/if}{if $item.class} {$item.class}{/if}  {if $item.iconclass}has-icon{/if} dropdown-item">
            <a {if $item.linkid}id="{$item.linkid}"{/if} {if $item.accesskey}accesskey="{$item.accesskey}" {/if}{if $item.aria}{foreach $item.aria key=key item=value}aria-{$key}="{$value}" {/foreach}{/if}href="{if $item.wwwroot}{$item.wwwroot}{else}{$WWWROOT}{/if}{$item.url}" class="{if $item.submenu}menu-dropdown-toggle{/if}">
                {if $item.iconclass}
                <span class="icon icon-{$item.iconclass}" role="presentation" aria-hidden="true"></span>
                {/if}
                <span class="nav-title">{$item.title}</span>
            </a>
            {if $item.submenu}
            <button type="button" class="navbar-showchildren navbar-toggle {if !$item.selected}collapsed{/if}" data-toggle="collapse" data-parent="#navuser" data-target="#userchildmenu-{$dwoo.foreach.menu.index}">
                 <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                 <span class="nav-title sr-only">{str tag="showmenu" section="mahara" arg1="$item.title"}</span>
            </button>
            {/if}
            {if $item.submenu}
            <ul id="userchildmenu-{$dwoo.foreach.menu.index}" class="{if $item.selected} show{/if} collapse child-nav" role="menu">
               {foreach from=$item.submenu item=subitem}
               <li class="{if $subitem.selected}active {/if}{if $subitem.submenu}has-sub {/if}">
                   <a href="{$WWWROOT}{$subitem.url}"{if $subitem.accesskey} accesskey="{$subitem.accesskey}"{/if}>
                       {$subitem.title}
                   </a>
                   {if $subitem.submenu}
                   <ul class="dropdown-tertiary">
                       {foreach from=$subitem.submenu item=tertiaryitem}
                       <li{if $tertiaryitem.selected} class="selected"{/if}>
                           <a href="{$WWWROOT}{$tertiaryitem.url}"{if $tertiaryitem.accesskey} accesskey="{$tertiaryitem.accesskey}"{/if}>
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
        <li class="btn-logout has-icon">
            <a id="logoutbutton" href="{$WWWROOT}?logout" accesskey="l">
                <span class="icon icon-sign-out" role="presentation" aria-hidden="true"></span>
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
{if !$nosearch && ($LOGGEDIN || $publicsearchallowed)}
<div class="navbar-form collapse navbar-collapse{if $languageform} with-langform{if !$LOGGEDIN && !$SHOWLOGINBLOCK && !$LOGINPAGE}-login{/if}{/if}">
    {header_search_form}
</div>
{/if}

{if $MAINNAV}

    <nav id="main-nav" class="no-site-messages {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}{if $DROPDOWNMENU}dropdown-adminnav navbar-default{else}adminnav navbar-inverse{/if}{else}navbar-inverse{/if} nav collapse navbar-collapse nav-main main-nav ">
       <div class="container">
           {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}
                <div class="navbar-header">
                    <a class="navbar-text navbar-link" href="{$WWWROOT}" accesskey="h" class="return-site">
                        <span class="icon icon-chevron-left"></span>
                        {str tag="returntosite"}
                    </a>
                </div>
            {/if}
            <ul id="{if $DROPDOWNMENU}dropdown-nav{else}nav{/if}" class="nav navbar-nav">


            {strip}
                {foreach from=$MAINNAV item=item name=menu}
                    <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} active{/if}{if $DROPDOWNMENU} dropdown-nav-home{/if}">
                            <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if} class="{if $item.path}{$item.path}{else}dashboard{/if}">
                                {if $item.accessibletitle && !$DROPDOWNMENU}
                                    <span aria-hidden="true" role="presentation">
                                {/if}
                                {$item.title}
                                {if $item.accessibletitle && !$DROPDOWNMENU}
                                    </span> <span class="accessible-hidden sr-only">
                                        ({$item.accessibletitle})
                                    </span>
                                {/if}
                                {if $DROPDOWNMENU && $item.submenu}
                                    <span class="accessible-hidden sr-only">
                                        ({str tag=dropdownmenu})
                                    </span>
                                {/if}

                            </a>
                            {if $item.submenu}
                                <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle {if !$item.selected}collapsed{/if}" data-toggle="collapse" data-target="#childmenu-{$dwoo.foreach.menu.index}">
                                    <span class="icon icon-chevron-down"></span>
                                    <span class="nav-title sr-only">{str tag="show"} {str tag="menu"}</span>
                                </button>
                            {/if}
                            {if $item.submenu}
                                <ul id="childmenu-{$dwoo.foreach.menu.index}" class="{if $DROPDOWNMENU}has-dropdown{else}hidden-md hidden-lg hidden-sm{/if}{if $item.selected} in{/if} collapse child-nav" role="menu">
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

                {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}
                {elseif $USER->get('admin')}
                    <li class="siteadmin">
                        <a href="{$WWWROOT}admin/" accesskey="a" class="admin-site">{str tag="administration"}</a>
                    </li>
                {elseif $USER->is_institutional_admin()}
                    <li class="instituteadmin">
                        <a href="{$WWWROOT}admin/users/search.php" accesskey="a" class="admin-user">{str tag="administration"}</a>
                    </li>
                {elseif $USER->get('staff')}
                    <li class="siteinfo">
                        <a href="{$WWWROOT}admin/users/search.php" accesskey="a" class="admin-user">{str tag="siteinformation"}</a>
                    </li>
                {elseif $USER->is_institutional_staff()}
                    <li class="instituteinfo">
                        <a href="{$WWWROOT}admin/users/search.php" accesskey="a" class="admin-user">{str tag="institutioninformation"}</a>
                    </li>
                {/if}
            {/strip}
            </ul>
        </div>
    </nav>

    {if $DROPDOWNMENU}
    {else}

        {if $SELECTEDSUBNAV}

        <div id="sub-nav" class="navbar navbar-default minor-nav hidden-xs">
            <div class="container">
                <ul class="nav navbar-nav">
                {strip}
                    {foreach from=$SELECTEDSUBNAV item=item}
                        <li{if $item.selected} class="active"{/if}>
                            <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title}</a>
                        </li>
                    {/foreach}
                {/strip}
                </ul>
            </div>
        </div>
        {/if}
    {/if}

{/if}

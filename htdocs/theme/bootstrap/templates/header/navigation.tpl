{if $MAINNAV}
 
    <nav id="main-nav" class="{if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}{if $DROPDOWNMENU}dropdown-adminnav {else}adminnav {/if}{/if} nav collapse navbar-collapse nav-main main-nav navbar-inverse">
       <div class="container">
           {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}
                <div class="navbar-header">
                    <span class="navbar-brand" href="">Administration <span class="glyphicon glyphicon-chevron-right"></span></span>
                </div>
            {/if}
            <ul id="{if $DROPDOWNMENU}dropdown-nav{else}nav{/if}" class="nav navbar-nav">


            {strip}
                {foreach from=$MAINNAV item=item}
                    <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} active{/if}{if $DROPDOWNMENU} dropdown-nav-home{/if}">
                            <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if} class="{if $item.path}{$item.path}{else}dashboard{/if}">
                                {if $item.accessibletitle && !$DROPDOWNMENU}
                                    <span aria-hidden="true" role="presentation">
                                {/if}
                                {$item.title}
                                {if $item.accessibletitle && !$DROPDOWNMENU}
                                    </span>
                                    <span class="accessible-hidden">
                                        ({$item.accessibletitle})
                                    </span>
                                {/if}
                                {if $DROPDOWNMENU && $item.submenu}
                                    <span class="accessible-hidden">
                                        ({str tag=dropdownmenu})
                                    </span>
                                {/if}
                            </a>
                        {if $DROPDOWNMENU}
                            {if $item.submenu}
                                <ul class="dropdown-menu" role="menu">
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
                                    <div class="cl"></div>
                                </ul>
                            {/if}
                        {/if}
                    </li>
                {/foreach}

                {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}
                    <li class="returntosite">
                        <a href="{$WWWROOT}" accesskey="h" class="return-site">{str tag="returntosite"}</a>
                    </li>
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

        <div class="navbar navbar-default navbar-secondary">
            <div class="container">
                <ul class="nav navbar-nav">
                {strip}
                    {foreach from=$SELECTEDSUBNAV item=item}
                        <li{if $item.selected} class="active"{/if}>
                            <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if}>{$item.title}</a>
                        </li>
                        {if $item.submenu && $item.selected}
                            {assign var=tertiarymenu value=$item.submenu}
                        {/if}
                    {/foreach}
                {/strip}
                </ul>
            </div>
        </div>
        {/if}
        {if $tertiarymenu}
        <div class="navbar navbar-secondary">
                <div class="container">
                    <ul class="nav navbar-nav">
                        {strip}
                            {foreach from=$tertiarymenu item=tertiaryitem}
                                <li{if $tertiaryitem.selected} class="active"{/if}>
                                    <a href="{$WWWROOT}{$tertiaryitem.url}"{if $tertiaryitem.accesskey} accesskey="{$tertiaryitem.accesskey}"{/if}>{$tertiaryitem.title}</a>
                                </li>
                            {/foreach}
                        {/strip}
                    </ul>
                </div>
        </div>
        {/if}
    {/if}

{/if}

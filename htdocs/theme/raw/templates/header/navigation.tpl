{if $MAINNAV}

    <nav id="main-nav" class="no-site-messages {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}adminnav navbar-inverse{else}navbar-inverse{/if} nav collapse navbar-collapse nav-main main-nav ">
       <div class="container">
            <ul id="nav" class="nav navbar-nav">

            {strip}
                {foreach from=$MAINNAV item=item name=menu}
                    <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} active{/if}">
                            <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if} class="{if $item.path}{$item.path}{else}dashboard{/if}">
                                {if $item.accessibletitle}
                                    <span aria-hidden="true" role="presentation" aria-hidden="true">
                                {/if}
                                {$item.title}
                                {if $item.accessibletitle}
                                    </span> <span class="accessible-hidden sr-only">
                                        ({$item.accessibletitle})
                                    </span>
                                {/if}
                            </a>
                            {if $item.submenu}
                                <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle {if !$item.selected}collapsed{/if}" data-toggle="collapse" data-target="#childmenu-{$dwoo.foreach.menu.index}">
                                    <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                                    <span class="nav-title sr-only">{str tag="show"} {str tag="menu"}</span>
                                </button>
                            {/if}
                            {if $item.submenu}
                                <ul id="childmenu-{$dwoo.foreach.menu.index}" class="{if $item.selected} in{/if} collapse child-nav" role="menu">
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
    <nav id="main-nav-admin" class="no-site-messages {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}adminnav navbar-inverse{else}navbar-inverse{/if} nav collapse navbar-collapse nav-main-admin main-nav-admin ">
       <div class="container">
         <ul id="navadmin" class="nav navbar-nav">

         {strip}
             {foreach from=$MAINNAVADMIN item=item name=menu}
                 <li class="{if $item.path}{$item.path}{else}dashboard{/if}{if $item.selected} active{/if}">
                         <a href="{$WWWROOT}{$item.url}"{if $item.accesskey} accesskey="{$item.accesskey}"{/if} class="{if $item.path}{$item.path}{else}dashboard{/if}">
                             {if $item.accessibletitle}
                                 <span aria-hidden="true" role="presentation" aria-hidden="true">
                             {/if}
                             {$item.title}
                             {if $item.accessibletitle}
                                 </span> <span class="accessible-hidden sr-only">
                                     ({$item.accessibletitle})
                                 </span>
                             {/if}
                         </a>
                         {if $item.submenu}
                             <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle {if !$item.selected}collapsed{/if}" data-toggle="collapse" data-target="#childmenu-{$dwoo.foreach.menu.index}">
                                 <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                                 <span class="nav-title sr-only">{str tag="show"} {str tag="menu"}</span>
                             </button>
                         {/if}
                         {if $item.submenu}
                             <ul id="childmenu-{$dwoo.foreach.menu.index}" class="{if $item.selected} in{/if} collapse child-nav" role="menu">
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

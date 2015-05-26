{* nav and beginning of page container for group info pages *}

<ul class="nav nav-pills nav-inpage {if $SIDEBARS}nav-stacked{/if}">
    {foreach from=$SUBPAGENAV item=item}
        <li class="{if $item.class}{$item.class} {/if}{if $item.selected} current-tab active{/if}">
            <a {if $item.tooltip}title="{$item.tooltip}"{/if} class="{if $item.selected} current-tab{/if}" href="{$WWWROOT}{$item.url}">
                {if $item.iconclass}<span class="{$item.iconclass} prs"></span>{/if}
                {$item.title}
                <span class="accessible-hidden sr-only">({str tag=tab}{if $item.selected} {str tag=selected}{/if})</span>
            </a>
        </li>
    {/foreach}
</ul>
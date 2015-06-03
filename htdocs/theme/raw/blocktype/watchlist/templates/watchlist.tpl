{if $watchlistempty}
<div class="panel-body">
    <p class="lead text-small">{str tag=nopages section=blocktype.watchlist}</p>
</div>
{else}
<ul id="watchlistblock" class="viewlist list-group">
    {foreach $views as item=view}
        <li class="{cycle values='r0,r1'} list-group-item list-group-item-link">
             <a href="{$view->fullurl}" class="watchlist-showview">
                {$view->title}
            </a>
        </li>
    {/foreach}
</ul>
{/if}

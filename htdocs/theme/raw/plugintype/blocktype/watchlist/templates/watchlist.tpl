{if $watchlistempty}
<div class="panel-body">
    <p class="lead text-small">{str tag=nopages section=blocktype.watchlist}</p>
</div>
{else}
<ul id="watchlistblock" class="viewlist list-group">
    {foreach $views as item=view}
        <li class="{cycle values='r0,r1'} list-group-item">
            <h4 class="title list-group-item-heading">
                 <a href="{$view->fullurl}" class="watchlist-showview">
                    {$view->title}
                </a>
            </h4>
        </li>
    {/foreach}
</ul>
{/if}

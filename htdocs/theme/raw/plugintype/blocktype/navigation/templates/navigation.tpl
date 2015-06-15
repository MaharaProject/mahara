{if $views}
    <ul id="collection-nav" class="list-group">
        {foreach from=$views item=item name=view}
            <li class=" list-group-item list-group-link{cycle name=rows values='r0,r1'}">
                {if $currentview == $item->view}
                    {$item->title}
                {else}
                   <a href="{$item->fullurl}">{$item->title}</a>
                {/if}
            </li>
        {/foreach}
    </ul>
{else}
<div class="panel-body">
    <p class="lead text-small">{str tag='noviewstosee' section='group'}</p>
</div>
{/if}

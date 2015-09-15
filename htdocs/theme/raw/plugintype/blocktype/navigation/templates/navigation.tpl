{if $views}
    <div id="collection-nav" class="list-group">
        {foreach from=$views item=item name=view}
            <div class=" list-group-item {cycle name=rows values='r0,r1'}">
                <h4 class="list-group-item-heading mb0">
                    {if $currentview == $item->view}
                        {$item->title}
                    {else}
                       <a href="{$item->fullurl}">{$item->title}</a>
                    {/if}
                </h4>
            </div>
        {/foreach}
    </div>
{else}
<div class="panel-body">
    <p class="lead text-small">{str tag='noviewstosee' section='group'}</p>
</div>
{/if}

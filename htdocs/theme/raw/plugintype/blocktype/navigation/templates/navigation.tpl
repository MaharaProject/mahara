{if $views}
    <div id="collection-nav" class="list-group">
        {foreach from=$views item=item name=view}
            <div class=" list-group-item">
                {if $currentview != $item->view}
                    <a href="{$item->fullurl}" class="outer-link">
                        <span class="sr-only">{$item->title}</span>
                    </a>
                {/if}
                <h4 class="list-group-item-heading">
                    {$item->title}
                </h4>
            </div>
        {/foreach}
    </div>
{else}
<div class="card-body">
    <p class="lead text-small">{str tag='noviewstosee' section='group'}</p>
</div>
{/if}

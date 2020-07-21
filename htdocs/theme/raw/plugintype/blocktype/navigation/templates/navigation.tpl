{if $views}
    <div id="collection-nav" class="list-group">
        {foreach from=$views item=item name=view}
            <div class="list-group-item flush">
                <h3 class="list-group-item-heading">
                  {if $currentview != $item->view}
                      <a href="{$item->fullurl}">
                  {/if}
                        {$item->title}
                  {if $currentview != $item->view}
                      </a>
                  {/if}
                </h3>
            </div>
        {/foreach}
    </div>
{else}
<div class="card-body">
    <p class="lead text-small">{str tag='noviewstosee' section='group'}</p>
</div>
{/if}

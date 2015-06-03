{if $VIEWS}
    <div id="userviewstable" class="list-group">
    {foreach from=$VIEWS item=item name=view}
            <div class="list-group-item">
                <h4 class="list-group-item-heading">
                    <a href="{$item.fullurl}">{$item.title}</a>
                </h4>
                {if $item.description}
                <p class="list-group-item-text">
                    {$item.description|str_shorten_html:100:true|strip_tags|safe}
                </p>
                {/if}
                {if $item.tags}
                <div class="tags">
                    <strong>{str tag=tags}:</strong> 
                    {list_tags owner=$item.owner tags=$item.tags}
                </div>
                {/if}
            </div>
    {/foreach}
    </div>
{else}
    {str tag='noviewstosee' section='group'}
{/if}


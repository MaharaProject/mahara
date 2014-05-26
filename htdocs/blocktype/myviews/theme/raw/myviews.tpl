{if $VIEWS}
    <div id="userviewstable" class="viewlist fullwidth listing">
    {foreach from=$VIEWS item=item name=view}
            <div class="{cycle values='r0,r1'} listrow">
                <h3 class="title"><a href="{$item.fullurl}">{$item.title}</a></h3>
                {if $item.description}
                  <div class="detail">{$item.description|str_shorten_html:100:true|strip_tags|safe}</div>
                {/if}
                {if $item.tags}
                  <div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$item.owner tags=$item.tags}</div>
                {/if}
            </div>
    {/foreach}
    </div>
{else}
    {str tag='noviewstosee' section='group'}
{/if}


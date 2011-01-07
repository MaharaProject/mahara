{if $VIEWS}
    <table id="userviewstable">
    {foreach from=$VIEWS item=item name=view}
        <tr>
            <td class="{cycle values='r0,r1'}">
                <h4><a href="{$WWWROOT}view/view.php?id={$item.id}">{$item.title}</a></h4>
                {if $item.description}
                  <div class="details">{$item.description|str_shorten_html:100:true|strip_tags|safe}</div>
                {/if}
                {if $item.tags}
                  <div class="tags s"><label>{str tag=tags}:</label> {list_tags owner=$item.owner tags=$item.tags}</div>
                {/if}
            </td>
        </tr>
    {/foreach}
    </table>
{else}
    {str tag='noviewstosee' section='group'}
{/if}


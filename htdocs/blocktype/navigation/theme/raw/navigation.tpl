{if $views}
    <table id="userviewstable" class="fullwidth">
    {foreach from=$views item=item name=view}
        <tr>
            <td class="{cycle values='r0,r1'}">
                {if $currentview == $item->view}
                    <h3 class="title">{$item->title}</h3>
                {else}
                    <h3 class="title"><a href="{$item->fullurl}">{$item->title}</a></h3>
                {/if}
            </td>
        </tr>
    {/foreach}
    </table>
{else}
    {str tag='noviewstosee' section='group'}
{/if}

{if $views}
    <table id="userviewstable">
    {foreach from=$views item=item name=view}
        <tr>
            <td class="{cycle values='r0,r1'}">
                {if $currentview == $item->view}
                    <h4>{$item->title}</h4>
                {else}
                    <h4><a href="{$WWWROOT}view/view.php?id={$item->view}">{$item->title}</a></h4>
                {/if}
            </td>
        </tr>
    {/foreach}
    </table>
{else}
    {str tag='noviewstosee' section='group'}
{/if}

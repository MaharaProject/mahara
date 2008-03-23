<div class="friends">
{if $friends}
    <table id="userfriendstable">
    {foreach from=$friends item=row}
        <tr>
        {foreach from=$row item=friend}
            <td class="r{cycle values=0,1} friendcell">
                <a href="{$WWWROOT}user/view.php?id={$friend}">
                   <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxwidth=60&amp;maxheight=60&amp;id={$friend}" alt="">
                   <br>{$friend|display_name|escape}
                </a>
            </td>
        {/foreach}
        </tr>
    {/foreach}
    </table>
{else}
    {str tag='nofriends' section='group'}
{/if}
</div>

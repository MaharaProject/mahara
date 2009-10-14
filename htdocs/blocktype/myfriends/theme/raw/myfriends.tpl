<div class="friends">
{if $friends}
    <table id="userfriendstable" class="center fullwidth">
    {foreach from=$friends item=row}
        <tr>
        {foreach from=$row item=friend}
            <td class="{cycle values='r0,r1'} friendcell">
                <a href="{$WWWROOT}user/view.php?id={$friend}">
                   <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxwidth=60&amp;maxheight=60&amp;id={$friend}" alt="">
                </a>
                <div><a href="{$WWWROOT}user/view.php?id={$friend}">{$friend|display_default_name|escape}</a></div>
            </td>
        {/foreach}
        </tr>
    {/foreach}
    </table>
{else}
    {if $lookingatownpage}
        <div class="message">{str tag="trysearchingforfriends" section=group args=$searchingforfriends}</div>
    {else}
        {if $relationship == 'none' && $friendscontrol == 'auto'}
            <div class="message">{$newfriendform}</div>
        {elseif $relationship == 'none' && $friendscontrol == 'auth'}
            <div class="message"><a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view" class="btn-request">{str tag='requestfriendship' section='group'}</a></div>
        {elseif $relationship == 'requestedfriendship'}
            <div class="message">{str tag=friendshiprequested section=group}</div>
        {/if}
        {* Case not covered here: friendscontrol disallows new users. The block will appear empty. *}
    {/if}
{/if}
</div>

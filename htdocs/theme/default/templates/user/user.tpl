{if $user->pending}
<td class="pending">
    <div class="fl">
        <img src="{$WWWROOT}thumb.php?type=profileicon&amp;size=40x40&amp;id={$user->id}" alt="">
    </div>
    <table class="friendinfo">
        <tr>
            <th>
                <div class="fr">
                    {$user->accept}
                </div>
                <h3>
                    <a href="{$WWWROOT}user/view.php?id={$user->id}">
                        {$user->id|display_name|escape}
                    </a>
                    - {str tag='pending' section='group'}
                </h3>
             </th>
        </tr>
        <tr>
            <td id="friendinfo_{$user->id}">
                {if $user->introduction}
                {$user->introduction|escape}
                {/if}
                <div class="pending">
                    <strong>
                        {str tag='whymakemeyourfriend' section='group'}
                        {$user->reason}
                    </strong>
                </div>
                <div>
                    <ul>
                        <li>
                            <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}">
                                {str tag='denyrequest' section='group'}
                            </a>
                        </li>
                        {if $user->messages}
                        <li>
                            <a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}">
                                {str tag='sendmessage' section='group'}
                            </a>
                        </li>
                        {/if}
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</td>
{elseif $user->friend}
<td>
    <div class="fl">
        <img src="{$WWWROOT}thumb.php?type=profileicon&amp;size=40x40&amp;id={$user->id}" alt="">
    </div>
    <table class="friendinfo">
        <tr>
            <th>
                <h3>
                    <a href="{$WWWROOT}user/view.php?id={$user->id}">
                        {$user->id|display_name|escape}
                    </a>
                    {if $page == 'find'}
                    - {str tag='existingfriend' section='group'}
                    {/if}
                </h3>
            </th>
            {if $page == 'myfriends'}
            <td rowspan="2" class="viewlist">
                <h3>
                    {str tag='Views' section='group'}
                </h3>
                {if $user->views}
                <ul>
                    {foreach from=$user->views item=view}
                    <li>
                    {$view}
                        <a href="{$WWWROOT}view/view.php?id={$view->id}">
                            {$view->title|escape}
                        </a>
                    </li>
                    {/foreach}
                </ul>
                {else}
                <p>
                    {str tag='noviewstosee' section='group'}
                </p>
                {/if}
            </td>
            {/if}
        </tr>
        <tr>
            <td id="friendinfo_{$user->id}">
                {if $user->introduction}
                <p>
                    {$user->introduction|escape}
                </p>
                {/if}
                <ul>
                {if $user->messages}
                    <li>
                        <a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}">
                            {str tag='sendmessage' section='group'}
                        </a>
                    </li>
                {/if}
                    <li>
                        <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}">
                            {str tag='removefromfriendslist' section='group'}
                        </a>
                    </li>
                </ul>
            </td>
        </tr>
    </table>
</td>
{elseif $user->requestedfriendship}
<td>
    <div class="fl">
        <img src="{$WWWROOT}thumb.php?type=profileicon&amp;size=40x40&amp;id={$user->id}" alt="">
    </div>
    <table class="friendinfo">
        <tr>
            <th>
                <div class="fr">
                    {str tag='friendshiprequested' section='group'}
                </div>
                <h3>
                    <a href="{$WWWROOT}user/view.php?id={$user->id}">
                        {$user->id|display_name|escape}
                    </a>
                </h3>
             </th>
        </tr>
        <tr>
            <td id="friendinfo_{$user->id}">
                {if $user->introduction}
                {$user->introduction|escape}
                {/if}
                <div>
                    <ul>
                        {if $user->messages}
                        <li>
                            <a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}">
                                {str tag='sendmessage' section='group'}
                            </a>
                        </li>
                        {/if}
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</td>
{else}
<td>
    <div class="fl">
        <img src="{$WWWROOT}thumb.php?type=profileicon&amp;size=40x40&amp;id={$user->id}" alt="">
    </div>
    <table class="friendinfo">
        <tr>
            <th>
                <div class="fr">
                    {if $user->friendscontrol == 'auth'}
                    <a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}">
                        {str tag='sendfriendrequest' section='group'}
                    </a>
                    {elseif $user->friendscontrol == 'auto'}
                        {$user->makefriend}
                    {else}
                        {str tag='userdoesntwantfriends' section='group'}
                    {/if}
                </div>
                <h3>
                    <a href="{$WWWROOT}user/view.php?id={$user->id}">
                        {$user->id|display_name|escape}
                    </a>
                </h3>
             </th>
        </tr>
        <tr>
            <td id="friendinfo_{$user->id}">
                {if $user->introduction}
                {$user->introduction|escape}
                {/if}
                <div>
                    <ul>
                        {if $user->messages}
                        <li>
                            <a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}">
                                {str tag='sendmessage' section='group'}
                            </a>
                        </li>
                        {/if}
                    </ul>
                </div>
            </td>
        </tr>
    </table>
</td>
{/if}

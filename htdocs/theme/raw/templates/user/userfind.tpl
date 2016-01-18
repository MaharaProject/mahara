<div class="list-group-item {if $user->pending} list-group-item-warning{/if}">
    <a href="{profile_url($user)}" class="outer-link">
        <span class="sr-only">{$user->display_name}</span>
     </a>
    <div class="row" id="friendinfo_{$user->id}">
        <div class="col-md-8">
            <div class="usericon-heading">
                <div class="user-icon pull-left">
                    <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
                </div>
                <h4 class="list-group-item-heading middle">
                    {$user->display_name}
                    {if $user->pending}
                    <span class="pendingfriend text-small text-midtone">
                        - {str tag='pending' section='group'}
                    </span>
                    {elseif $user->friend && $page == 'find'}
                    <span class="existingfriend text-small text-midtone">
                        - {str tag='existingfriend' section='group'}
                    </span>
                    {/if}
                </h4>
            </div>
            {if $user->institutions}
            <div class="memberof detail text-small">
                <span class="icon icon-lg text-default icon-university left" role="presentation" aria-hidden="true"></span>
                <span>
                {$user->institutions|safe}
            </div>
            {/if}
        </div>
        <div class="col-md-4">
            <ul class="list-unstyled inner-link text-small user-action-list">
                {if $user->pending}
                <li class="approvefriend">
                    <span class="icon icon-check icon-lg text-success" role="presentation" aria-hidden="true"></span>
                    {$user->accept|safe}
                </li>
                <li class="denyrequest">
                    <span class="icon icon-ban icon-lg text-danger left" role="presentation" aria-hidden="true"></span>
                    <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-deny">
                        {str tag='denyrequest' section='group'}
                    </a>
                </li>
                {/if}
                {if $user->friend}
                <li class="removefriend">
                    <span class="icon icon-lg text-danger icon-remove left" role="presentation" aria-hidden="true"></span>
                    <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-del">
                        {str tag='removefromfriendslist' section='group'}
                    </a>
                </li>
                {elseif $user->requestedfriendship}
                <li class="notbtn">
                    <span class="icon icon-lg text-success icon-check left" role="presentation" aria-hidden="true"></span>
                    <span>
                        {str tag='friendshiprequested' section='group'}
                    </span>
                </li>
                {elseif !$user->pending} {* Not an existing, pending, or requested friend *}
                {if $user->friendscontrol == 'auth'}
                <li class="friend">
                    <span class="icon icon-user-plus icon-lg left" role="presentation" aria-hidden="true"></span>
                    <a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-request">
                        {str tag='sendfriendrequest' section='group'}
                    </a>
                </li>
                {elseif $user->friendscontrol == 'auto'}
                <li class="friend">
                    <span class="icon icon-user-plus icon-lg left" role="presentation" aria-hidden="true"></span>
                    <a href="#addfriend{$user->id}_addfriend_submit" data-triggersubmit="addfriend{$user->id}_addfriend_submit">
                        {str tag='addtofriendslist' section='group'}
                    </a>
                    <div class="hidden">
                    {$user->makefriend|safe}
                    </div>
                </li>
                {else}
                <li class="nofriend">
                    {str tag='userdoesntwantfriends' section='group'}
                </li>
                {/if}
                {/if}
                {if $user->messages}
                <li class="send-message">
                    <span class="icon icon-envelope icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                    <a href="{$WWWROOT}{if $mrmoduleactive}module/multirecipientnotification{else}user{/if}/sendmessage.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-message">
                        {str tag='sendmessage' section='group'}
                    </a>
                </li>
                {/if}
                {if $admingroups}
                <li class="editgroup">
                    <span class="icon icon-lg text-default icon-cogs left" role="presentation" aria-hidden="true"></span>
                    <a id="editgroup-link" class="js-edit-group" href="" data-userid="{$user->id}">
                        {str tag='editgroupmembership' section='group'}
                    </a>
                </li>
                {/if}
            </ul>
        </div>
    </div>
</div>

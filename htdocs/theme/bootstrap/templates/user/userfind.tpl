<div class="listrow {if $user->pending} pending{/if}">
    <div class="pull-left" id="friendinfo_{$user->id}">
        <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" class="prs">
        <h3 class="title">
            <a href="{profile_url($user)}">
                {$user->display_name}
            </a>
            {if $user->pending}
            <span class="pendingfriend"> - {str tag='pending' section='group'}</span>
            {elseif $user->friend && $page == 'find'}
            <span class="existingfriend"> - {str tag='existingfriend' section='group'}</span>
            {/if}
        </h3>
    </div>
    <ul class="actionlist list-unstyled">
        {if $user->institutions}
        <li class="memberof mbm">
            {$user->institutions|safe}
        </li>
        {/if}
        {if $user->pending}
        <li class="approvefriend">{$user->accept|safe}</li>
        <li class="denyrequest">
            <span class="fa fa-ban"></span>
            <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-deny">
                {str tag='denyrequest' section='group'}
            </a>
        </li>
        {/if}
        {if $user->friend}
        <li class="removefriend">
            <span class="fa fa-remove"></span>
            <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-del">
                {str tag='removefromfriendslist' section='group'}
            </a>
        </li>
        {elseif $user->requestedfriendship}
        <li class="notbtn">
            <span class="">
                {str tag='friendshiprequested' section='group'}
            </span>
        </li>
        {elseif !$user->pending} {* Not an existing, pending, or requested friend *}
        {if $user->friendscontrol == 'auth'}
        <li class="friend">
            <span class="fa fa-user-plus"></span>
            <a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-request">
                {str tag='sendfriendrequest' section='group'}
            </a>
        </li>
        {elseif $user->friendscontrol == 'auto'}
        <li class="friend">
            {$user->makefriend|safe}
        </li>
        {else}
        <li class="nofriend">
            {str tag='userdoesntwantfriends' section='group'}
        </li>
        {/if}
        {/if}
        {if $user->messages}
        <li class="messages">
            <span class="fa fa-envelope"></span>
            <a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-message">
                {str tag='sendmessage' section='group'}
            </a>
        </li>
        {/if}
        {if $admingroups}
        <li class="editgroup">
            <span class="fa fa-pencil"></span>
            <a href="" onclick="showGroupBox(event, {$user->id})" class="btn-edit">{str tag='editgroupmembership' section='group'}</a>
        </li>
        {/if}
    </ul>
</div>
<div class="list-group-item {if $user->pending} list-group-item-warning{/if}">
    <a href="{profile_url($user)}" class="outer-link">
        <span class="sr-only">{$user->display_name}</span>
     </a>
    <div class="row" id="friendinfo_{$user->id}">
        <div class="col-sm-8">
            <div class="user-icon mts mrm pull-left">
                <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
            </div>
            <div class="pull-left">
                 <h4 class="list-group-item-heading">
                    {$user->display_name}
                    {if $user->pending}
                    <span class="pendingfriend metadata"> - {str tag='pending' section='group'}</span>
                    {elseif $user->friend && $page == 'find'}
                    <span class="existingfriend metadata"> - {str tag='existingfriend' section='group'}</span>
                    {/if}
                </h4>
                {if $user->institutions}
                <p class="memberof mtm detail text-small">
                    <span class="fa fa-lg text-default fa-university prs"></span>
                    <span>
                    {$user->institutions|safe}
                </p>
                {/if}
            </div>
        </div>
        <div class="col-sm-4">
            <ul class="list-unstyled inner-link">
                {if $user->pending}
                <li class="approvefriend">
                    <span class="fa fa-check fa-lg text-primary"></span>
                    {$user->accept|safe}
                </li>
                <li class="denyrequest">
                    <span class="fa fa-ban fa-lg text-danger prs"></span>
                    <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-deny">
                        {str tag='denyrequest' section='group'}
                    </a>
                </li>
                {/if}
                {if $user->friend}
                <li class="removefriend">
                    <span class="fa fa-lg text-danger fa-remove prs"></span>
                    <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-del">
                        {str tag='removefromfriendslist' section='group'}
                    </a>
                </li>
                {elseif $user->requestedfriendship}
                <li class="notbtn pbm">
                    <span>
                        {str tag='friendshiprequested' section='group'}
                    </span>
                </li>
                {elseif !$user->pending} {* Not an existing, pending, or requested friend *}
                {if $user->friendscontrol == 'auth'}
                <li class="friend">
                    <span class="fa fa-user-plus fa-lg text-success prs"></span>
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
                    <span class="fa fa-envelope fa-lg text-default prs"></span>
                    <a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-message">
                        {str tag='sendmessage' section='group'}
                    </a>
                </li>
                {/if}
                {if $admingroups}
                <li class="editgroup">
                    <span class="fa fa-lg text-default fa-cogs prs"></span>
                    <a href="" onclick="showGroupBox(event, {$user->id})" class="btn-edit">{str tag='editgroupmembership' section='group'}</a>
                </li>
                {/if}
            </ul>
        </div>
    </div>
</div>
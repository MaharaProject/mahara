<div class="{if $user->pending} pending panel-primary{else}panel-default {/if} panel panel-half">
    <h3 class="panel-heading has-link">
        <a href="{profile_url($user)}" class="pull-left">
            {$user->display_name}
            {if $user->pending}
            <span class="pendingfriend"> 
            <i>- {str tag='pending' section='group'}</i>
            </span>
            {elseif $user->friend && $page == 'find'}
            <span class="existingfriend"> 
            <i>- {str tag='existingfriend' section='group'}</i>
            </span>
            {/if}
        </a>
        {if $user->messages}
        <a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="text-right" title="{str tag='sendmessage' section='group'}">
            <span class="fa fa-envelope"></span>
            <span class="sr-only">{str tag='sendmessage' section='group'}</span>
        </a>
        {/if}
    </h3>
    <div class="panel-body">
        <div class="user-icon" id="friendinfo_{$user->id}">
            <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" class="prm">
        </div>
        <div class="content ptm pbm">
            {if $user->introduction}
            <p class="intro">
                {$user->introduction|str_shorten_html:100:true|safe}
            </p>
            {/if}
            
            {if $user->friend && $page == 'myfriends' && $user->views}
            <p class="viewlist">
                <span>
                    <strong>{str tag='Views' section='group'}:</strong>
                </span>
                {foreach from=$user->views item=view}
                <span>
                    <a href="{$view->fullurl}">{$view->title}</a>
                </span>
                {/foreach}
            </p>
            {/if}
            
            {if $user->pending}
            <div class="whymakemeyourfriend">
                <strong>
                    {str tag='whymakemeyourfriend' section='group'}
                </strong>
                <p>{$user->message|format_whitespace|safe}</p>
            </div>
            {/if}
        </div>
        {if $user->institutions}
        <span class="memberof ptm">
            <span class="fa fa-university"></span>
            {$user->institutions|safe}
        </span>
        {/if}
    </div>

    <div class="panel-footer has-form small-text">
        <div class="pull-left">
        {if $user->pending}
            <span class="approvefriend">
                {$user->accept|safe}
            </span>
            <span class="denyrequest">
                <span class="fa fa-ban"></span>
                <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-deny">
                    {str tag='denyrequest' section='group'}
                </a>
            </span>
        {/if}
        {if $user->friend}
            <span class="removefriend">
                <span class="fa fa-remove"></span>
                <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-del">
                    {str tag='removefromfriendslist' section='group'}
                </a>
            </span>
        {/if}
        </div>
        
        <div class="pull-left">
        {if $user->requestedfriendship}
            <span class="notbtn">
                {str tag='friendshiprequested' section='group'}
            </span>
        {elseif !$user->pending && !$user->friend} {* Not an existing, pending, or requested friend *}
            {if $user->friendscontrol == 'auth'}
            <span class="friend">
                <span class="fa fa-user-plus"></span>
                <a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-request">
                    {str tag='sendfriendrequest' section='group'}
                </a>
            </span>
            {elseif $user->friendscontrol == 'auto'}
            <span class="friend">
                {$user->makefriend|safe}
            </span>
            {else}
            <span class="nofriend">
                    {str tag='userdoesntwantfriends' section='group'}
            </span>
            {/if}
        {/if}
        </div>

        <div class="pull-right">
        {if $admingroups}
            <span class="editgroup">
                <span class="fa fa-pencil"></span>
                <a href="" onclick="showGroupBox(event, {$user->id})" class="btn-edit">{str tag='editgroupmembership' section='group'}</a>
            </span>
        {/if}
        </div>
    </div>
</div>
<div class="list-group-item {if $user->pending} list-group-item-warning{/if}">
    <div class="row" id="friendinfo_{$user->id}">
        <div class="col-md-8">
            <div class="user-icon user-icon-60 float-start">
                <img src="{profile_icon_url user=$user maxwidth=60 maxheight=60}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
            </div>
            <h3 class="list-group-item-heading">
                <a href="{profile_url($user)}">
                    {$user->display_name}
                </a>
                {if $user->pending}
                <span class="pendingfriend text-regular text-midtone font-base">
                    - {str tag='pendingsince' section='group' arg1=$user->pending_time}
                </span>
                {elseif $user->friend && $page == 'find'}
                <span class="existingfriend text-regular text-midtone font-base">
                    - {str tag='existingfriend' section='group'}
                </span>
                {/if}
            </h3>
            {if $user->friend && $user->views}
            <p class="viewlist detail text-small push-left-for-usericon-60">
                <strong>
                    {str tag='Portfolios' section='view'}:
                </strong>
                {foreach from=$user->views item=view name=addr}
                <a href="{$view->fullurl}">{$view->title}</a>{if !$dwoo.foreach.addr.last}, {/if}
                {/foreach}
            </p>
            {/if}
            {if $user->institutions}
            <div class="memberof detail text-small push-left-for-usericon-60">
                <span class="icon text-default icon-university left" role="presentation" aria-hidden="true"></span>
                {$user->institutions|safe}
            </div>
            {/if}
            {if $user->introduction}
            <div class="text-small detail text-midtone push-left-for-usericon-60">
                <a class="text-link collapsed with-introduction" data-bs-toggle="collapse" data-bs-target="#userintro{$user->id}"
                    href="#userintro{$user->id}" role="button" aria-expanded="false"
                    aria-controls="userintro{$user->id}">
                    {str tag=showintroduction section=group}
                    <span class="icon icon-chevron-down collapse-indicator text-inline" role="presentation" aria-hidden="true"></span>
                </a>
            </div>
            <div class="introduction detail text-small push-left-for-usericon-60">
                <div class="collapse" id="userintro{$user->id}">
                    {$user->introduction|safe}
                </div>
            </div>
            {/if}
        </div>
        <div class="col-md-4">
            <ul class="list-unstyled user-action-list text-small">
                {if $user->pending}
                <span class="whymakemeyourfriend">
                    <strong>
                        {str tag="pendingfriend" section="group"}
                    </strong>
                    <br />
                    {str tag='whymakemeyourfriend' section='group'}
                    <p>{$user->message|format_whitespace|safe}</p>
                </span>
                <li class="acceptdenyfriendbtns">
                    <div class="btn-group">
                        {$user->accept|safe}
                        <button data-url="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" type="submit" class="btn btn-secondary btn-sm">
                            <span class="icon icon-ban text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag='deny' section='group'}
                        </button>
                    </div>
                </li>
                {/if}
                {if $user->friend}
                <li class="removefriend">
                    <span class="icon text-danger icon-user-times left" role="presentation" aria-hidden="true"></span>
                    <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}">
                        {str tag='removefromfriendslist' section='group'}
                    </a>
                </li>
                {elseif $user->requestedfriendship}
                <li class="notbtn">
                    <span class="icon text-success icon-check left" role="presentation" aria-hidden="true"></span>
                    <span>
                        {str tag='friendshiprequested' section='group'}
                    </span>
                </li>
                {elseif !$user->pending} {* Not an existing, pending, or requested friend *}
                {if $user->friendscontrol == 'auth'}
                <li class="friend">
                    <span class="icon icon-user-plus left" role="presentation" aria-hidden="true"></span>
                    <a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}">
                        {str tag='sendfriendrequest' section='group'}
                    </a>
                </li>
                {elseif $user->friendscontrol == 'auto'}
                <li class="friend">
                    <span class="icon icon-user-plus left" role="presentation" aria-hidden="true"></span>
                    <a href="#addfriend{$user->id}_addfriend_submit" data-bs-triggersubmit="addfriend{$user->id}_addfriend_submit">
                        {str tag='addtofriendslist' section='group'}
                    </a>
                    <div class="d-none">
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
                    <span class="icon icon-envelope text-default left" role="presentation" aria-hidden="true"></span>
                    <a href="{$WWWROOT}{if $mrmoduleactive}module/multirecipientnotification{else}user{/if}/sendmessage.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-message">
                        {str tag='sendmessage' section='group'}
                    </a>
                </li>
                {/if}
                {if $admingroups}
                <li class="editgroup">
                    <span class="icon text-default icon-cogs left" role="presentation" aria-hidden="true"></span>
                    <a id="editgroup-link" class="js-edit-group" href="" data-userid="{$user->id}">
                        {str tag='editgroupmembership' section='group'}
                    </a>
                </li>
                {/if}
                {if $user->show_masquerade}
                <li class="masquerade">
                    <span class="icon text-default icon-user-secret left" role="presentation" aria-hidden="true"></span>
                    <a class="js-masquerade" href="{$WWWROOT}admin/users/changeuser.php?id={$user->id}">
                        {str tag='masqueradeas' section='admin'}
                    </a>
                </li>
                {/if}
            </ul>
        </div>
    </div>
</div>

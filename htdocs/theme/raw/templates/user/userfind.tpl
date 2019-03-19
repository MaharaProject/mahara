<div class="list-group-item {if $user->pending} list-group-item-warning{/if}">
    <div class="row" id="friendinfo_{$user->id}">
        <div class="col-md-8">
            <div class="usericon-heading">
                <div class="user-icon user-icon-40 float-left">
                    <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
                </div>
                <h4 class="list-group-item-heading middle">
                    <a href="{profile_url($user)}" >
                        <span class="sr-only">{$user->display_name}</span>
                        {$user->display_name}
                    </a>
                    {if $user->pending}
                    <span class="pendingfriend text-small text-midtone">
                        - {str tag='pendingsince' section='group' arg1=$user->pending_time}
                    </span>
                    {elseif $user->friend && $page == 'find'}
                    <span class="existingfriend text-small text-midtone">
                        - {str tag='existingfriend' section='group'}
                    </span>
                    {/if}

                    {if $user->friend && $user->views}
                    <p class="viewlist">
                        <strong>
                            {str tag='Portfolios' section='view'}:
                        </strong>
                        {foreach from=$user->views item=view name=addr}
                        <a href="{$view->fullurl}">{$view->title}</a>{if !$dwoo.foreach.addr.last}, {/if}
                        {/foreach}
                    </p>
                    {/if}

                </h4>
                {if $user->institutions}
                <div class="memberof detail text-small">
                    <span class="icon icon-lg text-default icon-university left" role="presentation" aria-hidden="true"></span>
                    {$user->institutions|safe}
                </div>
                {/if}
            </div>
        </div>
        <div class="col-md-4">
            <ul class="list-unstyled inner-link user-action-list">
                {if $user->pending}
                <p class="list-group-item-heading">{str tag="pendingfriend" section="group"}</p>
                <span class="whymakemeyourfriend text-small">
                    <strong>
                        {str tag='whymakemeyourfriend' section='group'}
                    </strong>
                    <p>{$user->message|format_whitespace|safe}</p>
                </span>
                <li>
                    <div class="btn-group">
                        {$user->accept|safe}
                        <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn btn-secondary">
                            <span class="icon icon-ban icon-lg text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag='deny' section='group'}
                        </a>
                    </div>
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

<div class="{if $user->pending}pending panel-warning{else}panel-default{/if} panel panel-half myfriend">
    <h3 class="panel-heading profile-block">
        <a href="{profile_url($user)}" class="username">
            {$user->display_name}
            {if $user->pending}
            <em class="text-small pendingfriend">
            - {str tag='pendingsince' section='group' arg1=$user->pending_time}
            </em>
            {elseif $user->friend && $page == 'find'}
            <em class="text-small existingfriend">
            - {str tag='existingfriend' section='group'}
            </em>
            {/if}
        </a>
        <span id="friendinfo_{$user->id}">
            <img class="user-icon" src="{profile_icon_url user=$user maxwidth=60 maxheight=60}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
        </span>
    </h3>
    <div class="panel-body">
        {if $user->institutions}
        <div class="memberof detail with-user-icon-lg">
            <span class="icon icon-university left" role="presentation" aria-hidden="true"></span>
            {$user->institutions|safe}
        </div>
        {/if}
        <div class="user-content content-text">
            {if $user->introduction}
            <p class="intro">
                {$user->introduction|str_shorten_html:100:true|safe}
            </p>
            {/if}

            {if $user->friend && $page == 'myfriends' && $user->views}
            <p class="viewlist">
                <strong>
                    {str tag='Views' section='group'}:
                </strong>
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
        {if $user->messages}
        <div class="sendmessage text-small">
            <span class="icon icon-envelope left" role="presentation" aria-hidden="true"></span>
            <a href="{$WWWROOT}{if $mrmoduleactive}module/multirecipientnotification{else}user{/if}/sendmessage.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="text-right" title="{str tag='sendmessage' section='group'}">
            {str tag='sendmessage' section='group'}
            </a>
        </div>
        {/if}
        {if $admingroups}
        <div class="editgroup text-small">
            <span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>
            <a href="" id="editgroup-link-{$user->id}" class="js-edit-group" data-userid="{$user->id}">
                {str tag='editgroupmembership' section='group'}
            </a>
        </div>
        {/if}
    </div>

    {if $user->pending}
    <div class="has-form text-small panel-footer btn-toggle">
        <div class="btn">
            <span class="icon icon-check icon-lg text-success left" role="presentation" aria-hidden="true"></span>
            {$user->accept|safe}
        </div>

        <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn">
            <span class="icon icon-ban left icon-lg text-danger" role="presentation" aria-hidden="true"></span>
            <span class="link-unstyled">{str tag='denyrequest' section='group'}</span>
        </a>
    </div>
    {/if}
    {if $user->friend}
        <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="panel-footer">
            <span class="icon icon-remove left icon-lg text-danger" role="presentation" aria-hidden="true"></span>
            {str tag='removefromfriendslist' section='group'}
        </a>
    {/if}

</div>

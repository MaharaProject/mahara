<div class="{if $user->pending}pending panel-warning{else}panel-default{/if} panel panel-half myfriend">
    <h3 class="panel-heading profile-block">
        <a href="{profile_url($user)}" class="prxxl mrl">
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
        <span class="user-icon" id="friendinfo_{$user->id}">
            <img src="{profile_icon_url user=$user maxwidth=60 maxheight=60}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
        </span>
    </h3>
    <div class="panel-body">
        {if $user->institutions}
        <div class="memberof detail with-user-icon-lg">
            <span class="icon icon-university prs"></span>
            {$user->institutions|safe}
        </div>
        {/if}
        <div class="content pbm">
            {if $user->introduction}
            <p class="intro">
                {$user->introduction|str_shorten_html:100:true|safe}
            </p>
            {/if}

            {if $user->friend && $page == 'myfriends' && $user->views}
            <p class="viewlist">
                <span class="lead text-small">
                    {str tag='Views' section='group'}:
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
                <span class="lead text-small">
                    {str tag='whymakemeyourfriend' section='group'}
                </span>
                <p>{$user->message|format_whitespace|safe}</p>
            </div>
            {/if}
        </div>
        {if $user->messages}
        <div class="sendmessage text-small">
            <span class="icon icon-envelope prs"></span>
            <a href="{$WWWROOT}{if $mrmoduleactive}module/multirecipientnotification{else}user{/if}/sendmessage.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="text-right" title="{str tag='sendmessage' section='group'}">
            {str tag='sendmessage' section='group'}
            </a>
        </div>
        {/if}
        {if $admingroups}
        <div class="editgroup text-small">
            <span class="icon icon-pencil prs"></span>
            <a href="" onclick="showGroupBox(event, {$user->id})" class="btn-edit">{str tag='editgroupmembership' section='group'}</a>
        </div>
        {/if}
    </div>

    {if $user->pending}
    <div class="has-form text-small panel-footer btn-toggle">
        <div class="btn">
            <span class="icon icon-check icon-lg text-success prs"></span>
            {$user->accept|safe}
        </div>

        <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn">
            <span class="icon icon-ban prs icon-lg text-danger"></span>
            <span class="link-unstyled">{str tag='denyrequest' section='group'}</span>
        </a>
    </div>
    {/if}
    {if $user->friend}
        <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="panel-footer">
            <span class="icon icon-remove prs icon-lg text-danger"></span>
            {str tag='removefromfriendslist' section='group'}
        </a>
    {/if}

</div>
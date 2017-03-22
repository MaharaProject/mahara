<div class="peoplelistinfo list-group-item clearfix">
    <div class="usericon-heading">
        <span class="user-icon small-icon pull-left">
            <img id="staffinfo_{$user->id}" src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" class="pull-left">
        </span>
        <h4 class="list-group-item-heading">
            <a href="{profile_url($user)}">{$user->display_name}</a>
        </h4>
        {if $USER->get('id') != $user->id && $user->messages && $listtype == 'admin'}
            <a href="{$WWWROOT}{if $mrmoduleactive}module/multirecipientnotification{else}user{/if}/sendmessage.php?id={$user->id}&returnto={$page}&inst={$inst}" id="btn-sendmessage" class="btn btn-default btn-xs">
                <span class="icon icon-envelope left" aria-hidden="true" role="presentation"></span>
                {str tag='sendmessage' section='group'}
            </a>
        {/if}
    </div>
</div>


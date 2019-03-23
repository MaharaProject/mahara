<div class="pageactions">
    <div class="btn-group-vertical">
        {if $ownprofile}
            <a title="{str tag=editthisview section=view}" href="{$WWWROOT}view/blocks.php?profile=1" class="btn btn-secondary">
                <span class="icon-pencil icon icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag=editthisview section=view}</span>
            </a>
        {/if}
        {if $loginas}
            <a href="{$WWWROOT}admin/users/changeuser.php?id={$USERID}" class="btn-login btn btn-secondary" title="{$loginas}">
                <span class="icon-user-secret icon icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{$loginas}</span>
            </a>

            {if $USER->get('admin')}
            <a href="{$WWWROOT}admin/users/edit.php?id={$USERID}" class="btn-edit btn btn-secondary" title="{str tag=accountsettings section=admin}">
                <span class="icon-cogs icon icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag=accountsettings section=admin}</span>
            </a>
            {/if}
        {/if}

        {if $canmessage}
        <a href="{$WWWROOT}{if $mrmoduleactive}module/multirecipientnotification{else}user{/if}/sendmessage.php?id={$USERID}&amp;returnto=view" class="btn-message btn btn-secondary" title="{str tag='sendmessage' section='group'}">
            <span class="icon-envelope icon icon-lg left" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag='sendmessage' section='group'}</span>
        </a>
        {/if}

        {if $relationship == 'existingfriend'}
            <a href="{$WWWROOT}user/removefriend.php?id={$USERID}&amp;returnto=view" class="btn-del btn btn-secondary" title="{str tag='removefromfriendslist' section='group'}">
                <span class="icon-user-times icon icon-lg left text-danger" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag='removefromfriendslist' section='group'}</span>
            </a>

        {elseif $relationship == 'none' && $friendscontrol == 'auto'}

        {$newfriendform|safe}

        {elseif $relationship == 'none' && $friendscontrol == 'auth'}
        <a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view" class="btn-friend btn btn-secondary" title="{str tag='requestfriendship' section='group'}">
            <span class="icon-user-plus icon icon-lg left" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag='requestfriendship' section='group'}</span>
        </a>
        {/if}
    </div>
</div>

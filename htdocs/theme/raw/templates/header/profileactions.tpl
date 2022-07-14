<div class="pageactions">
    <div class="btn-group-vertical">
        {if $ownprofile}
            <button title="{str tag=editthisview section=view}" data-url="{$WWWROOT}view/blocks.php?profile=1" type="button" class="btn btn-secondary">
                <span class="icon-pencil-alt icon icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{str tag=editthisview section=view}</span>
            </button>
        {/if}
        {if $loginas}
            <button data-url="{$WWWROOT}admin/users/changeuser.php?id={$USERID}" type="submit" class="btn-login btn btn-secondary" title="{$loginas}">
                <span class="icon-user-secret icon icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{$loginas}</span>
            </button>

            {if $adminforuser}
            <button data-url="{$WWWROOT}admin/users/edit.php?id={$USERID}" class="btn btn-secondary" type="button" title="{str tag=accountsettings section=admin}">
                <span class="icon-cogs icon icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{str tag=accountsettings section=admin}</span>
            </button>
            {/if}
        {/if}

        {if $canmessage}
        <button data-url="{$WWWROOT}{if $mrmoduleactive}module/multirecipientnotification{else}user{/if}/sendmessage.php?id={$USERID}&amp;returnto=view" type="button" class="btn-message btn btn-secondary" title="{str tag='sendmessage' section='group'}">
            <span class="icon-envelope icon icon-lg left" role="presentation" aria-hidden="true"></span>
            <span class="visually-hidden">{str tag='sendmessage' section='group'}</span>
        </button>
        {/if}

        {if $relationship == 'existingfriend'}
            <button data-url="{$WWWROOT}user/removefriend.php?id={$USERID}&amp;returnto=view" class="btn btn-secondary" title="{str tag='removefromfriendslist' section='group'}">
                <span class="icon-user-times icon icon-lg left text-danger" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{str tag='removefromfriendslist' section='group'}</span>
            </button>

        {elseif $relationship == 'none' && $friendscontrol == 'auto'}

        {$newfriendform|safe}

        {elseif $relationship == 'none' && $friendscontrol == 'auth'}
        <button data-url="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view" class="btn-friend btn btn-secondary" type="button" title="{str tag='requestfriendship' section='group'}">
            <span class="icon-user-plus icon icon-lg left" role="presentation" aria-hidden="true"></span>
            <span class="visually-hidden">{str tag='requestfriendship' section='group'}</span>
        </button>
        {/if}
    </div>
</div>

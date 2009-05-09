{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
        <div id="userview">
                <div class="user-icon right">
                    {if !empty($loginas)}
                      <strong><a href="{$WWWROOT}admin/users/changeuser.php?id={$USERID}">{$loginas}</a></strong>
                    {/if}
                    {if $canmessage}
                        <a href="{$WWWROOT}user/sendmessage.php?id={$USERID}&amp;returnto=view" id="btn-sendmessage">{str tag='sendmessage' section='group'}</a>
                    {/if}
                    {if $relationship == 'existingfriend'}
                        
                        <a href="{$WWWROOT}user/removefriend.php?id={$USERID}&amp;returnto=view" id="btn-delete">{str tag='removefromfriendslist' section='group'}</a>
                    {elseif $relationship == 'none' && $friendscontrol == 'auto'}
                        {$newfriendform}
                    {elseif $relationship == 'none' && $friendscontrol == 'auth'}
                        
                        <a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view" class="btn-request">{str tag='requestfriendship' section='group'}</a>
                    {/if}
                    {if $invitedlist}<div>{str tag=groupinvitesfrom section=group}{$invitedlist}</div>{/if}
                    {if $inviteform}
                        {$inviteform}
                    {/if}
                    {if $requestedlist}<div>{str tag=requestedmembershipin section=group}{$requestedlist}</div>{/if}
                    {if $addform}
                        {$addform}
                    {/if}
                    {if $togglepublic}
                        {$togglepublic}
                    {/if}
                </div>
                {if $relationship == 'pending'}
                    <div class="message">
                        {str tag='whymakemeyourfriend' section='group'} {$message|escape}
                        {$requestform}
                    </div>
                {/if}
            </div>
    <div id="view" class="cl">
        <div id="bottom-pane">
            <div id="column-container">
               {$viewcontent}
                <div class="cb">
                </div>
            </div>
        </div>
    </div>

{include file="columnleftend.tpl"}

{include file="footer.tpl"}

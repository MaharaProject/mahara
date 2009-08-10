{include file="header.tpl"}
                    <div id="userview">
                        <div class="user-icon right">
                            {$institutions}
{if !empty($loginas)}                      <strong><a href="{$WWWROOT}admin/users/changeuser.php?id={$USERID}">{$loginas}</a></strong>
{/if}
{if $canmessage}                        <a href="{$WWWROOT}user/sendmessage.php?id={$USERID}&amp;returnto=view" class="btn-msg">{str tag='sendmessage' section='group'}</a>
{/if}
{if $relationship == 'existingfriend'}
                            <a href="{$WWWROOT}user/removefriend.php?id={$USERID}&amp;returnto=view" class="btn-del">{str tag='removefromfriendslist' section='group'}</a>
{elseif $relationship == 'none' && $friendscontrol == 'auto'}
                            {$newfriendform}
{elseif $relationship == 'none' && $friendscontrol == 'auth'}
                            <a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view" class="btn-req">{str tag='requestfriendship' section='group'}</a>
{/if}
{if $invitedlist}                            <div>{str tag=groupinvitesfrom section=group}{$invitedlist}</div>
{/if}
{if $inviteform}                            <div class="btn-msg">{$inviteform}</div>
{/if}
{if $requestedlist}                            <div>{str tag=requestedmembershipin section=group}{$requestedlist}</div>
{/if}
                            <div class="btn-add">{$addform}</div>
                            <div class="btn-bold btn-edit">{$togglepublic}</div>
                    </div>
{if $relationship == 'pending'}
                    <div class="message">
                        {str tag='whymakemeyourfriend' section='group'} {$message|escape}
                        {$requestform}
                    </div>
{/if}
                </div>
                <iframe id="viewframe" src="{$WWWROOT}/view/view.php?id={$profileviewid}&profileframe=1" width="100%" frameborder="0" marginheight="0" marginwidth="0"></iframe>
{include file="footer.tpl"}

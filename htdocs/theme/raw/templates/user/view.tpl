{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}{if $pageheadinghtml}<h1>{$pageheadinghtml|safe}</h1>{/if}
{/if}


{if $relationship == 'pending'}
                    	<div class="message">
                            {str tag='whymakemeyourfriend' section='group'} {$message}
                            {$requestform|safe}
                    	</div>
{/if}
                    <div id="userview">
                        <div class="user-icon">
                            {$institutions}
{if $loginas}
							<a href="{$WWWROOT}admin/users/changeuser.php?id={$USERID}" class="btn-login">{$loginas}</a>
    {if $USER->get('admin')}<a href="{$WWWROOT}admin/users/edit.php?id={$USERID}" class="btn-edit">{str tag=accountsettings section=admin}</a>{/if}
{/if}
{if $canmessage}
							<a href="{$WWWROOT}user/sendmessage.php?id={$USERID}&amp;returnto=view" class="btn-message">{str tag='sendmessage' section='group'}</a>
{/if}
{if $relationship == 'existingfriend'}
                            <a href="{$WWWROOT}user/removefriend.php?id={$USERID}&amp;returnto=view" class="btn-del">{str tag='removefromfriendslist' section='group'}</a>
{elseif $relationship == 'none' && $friendscontrol == 'auto'}
                            {$newfriendform|safe}
{elseif $relationship == 'none' && $friendscontrol == 'auth'}
                            <a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view" class="btn-friend">{str tag='requestfriendship' section='group'}</a>
{/if}
{if $invitedlist}
							<div>{str tag=groupinvitesfrom section=group}{$invitedlist}</div>
{/if}
{if $inviteform}
							<span class="addform">{$inviteform|safe}</span>
{/if}
{if $requestedlist}
							<div>{str tag=requestedmembershipin section=group}{$requestedlist}</div>
{/if}
                            {if $addform}<span class="addform">{$addform|safe}</span>{/if}
                            <div class="cb"></div>
                    	</div>
                	</div>
                	<div id="view" class="cl">
                    	<div id="bottom-pane">
                    	    <div id="column-container">
                                {$viewcontent|safe}
                        	    <div class="cb"></div>
                        	</div>
                    	</div>
                    </div>
{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}


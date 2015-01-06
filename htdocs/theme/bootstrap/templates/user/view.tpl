{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
  <h1 class="user-icon-name">
  <span class="usericon"><img src="{profile_icon_url user=$user maxwidth=60 maxheight=60}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" /></span>
  {if $pageheadinghtml}
    {$pageheadinghtml|safe}
  {/if}
  </h1>
  {if $ownprofile}
  <div class="rbuttons userviewrbuttons">
    <a title="{str tag=editthisview section=view}" href="{$WWWROOT}view/blocks.php?profile=1" class="btn">{str tag=editthisview section=view}</a>
  </div>
  {/if}
{/if}


{if $relationship == 'pending'}
                    	<div class="message attentionmessage">
                            <strong>{str tag='whymakemeyourfriend' section='group'}</strong> {$message}
                            <div class="attentionform">
                              {$acceptform|safe}
                              <a class="btn" id="approve_deny_friendrequest_deny" href="{$WWWROOT}user/denyrequest.php?id={$USERID}&returnto=view">{str tag=denyrequest section=group}</a>
                            </div>
                    	</div>
{/if}
                    <div id="userview">
                        <div class="user-icon">
                            {$institutions|safe}
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
                    </div>
{if $invitedlist}
						<span class="invitedtojoin"><strong>{str tag=groupinvitesfrom section=group}</strong> {$invitedlist}</span>
{/if}
{if $inviteform}
						<span class="addform">{$inviteform|safe}</span>
{/if}

{if $requestedlist}
						<span class="requestedmembership"><strong>{str tag=requestedmembershipin section=group}</strong> {$requestedlist}
{/if}
{if $addform}
						<span class="addform">{$addform|safe}</span>
{/if}

                        <div class="cb"></div>
                	</div>
                	<div id="view" class="cl">
                    	<div id="bottom-pane">
                    	    <div id="column-container">
                                {if $restrictedview}
                                    <strong>{str tag=profilenotshared section=view}</strong>
                                {else}
                                    {$viewcontent|safe}
                                {/if}
                        	    <div class="cb"></div>
                        	</div>
                    	</div>
                    </div>
{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}


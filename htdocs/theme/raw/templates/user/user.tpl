<td class="friendinfo{if $user->pending} pending rel{/if}">
	<ul class="actionlist">
      {if $user->institutions}<li>{$user->institutions}</li>{/if}
      {if $user->pending}
		<li>{$user->accept|safe}</li>
		<li>
			<a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}" id="btn-denyrequest">
				{str tag='denyrequest' section='group'}
			</a>
		</li>
      {/if}
      {if $user->messages}
		<li>
			<a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}" id="btn-sendmessage">
				{str tag='sendmessage' section='group'}
			</a>
		</li>
      {/if}
      {if $user->friend}
		<li>
			<a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}" id="btn-del">
				{str tag='removefromfriendslist' section='group'}
			</a>
		</li>
      {elseif $user->requestedfriendship}
		<li>
			{str tag='friendshiprequested' section='group'}
		</li>
      {elseif !$user->pending} {* Not an existing, pending, or requested friend *}
		<li>
			{if $user->friendscontrol == 'auth'}
			<a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}" id="btn-request">
				{str tag='sendfriendrequest' section='group'}
			</a>
			{elseif $user->friendscontrol == 'auto'}
				{$user->makefriend|safe}
			{else}
				{str tag='userdoesntwantfriends' section='group'}
			{/if}
		</li>
      {/if}
      {if $admingroups->invite || $admingroups->controlled}
      <li><a href="" onclick="showGroupBox(event, {$user->id})">{str tag='editgroupmembership' section='group'}</a></li>
      {/if}
	</ul>

	<div class="leftdiv" id="friendinfo_{$user->id}">
        <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="">
	</div>

	<div class="rightdiv">
      <h3>
		<a href="{$WWWROOT}user/view.php?id={$user->id}">{$user->display_name}</a>
		{if $user->pending}- {str tag='pending' section='group'}
        {elseif $user->friend && $page == 'find'}- {str tag='existingfriend' section='group'}
        {/if}
      </h3>
    {if $user->friend && $page == 'myfriends' && $user->views}
      <ul class="viewlist">
			<li class="label">
				<strong>{str tag='Views' section='group'}</strong>
			</li>
			{foreach from=$user->views item=view}
			<li>
				<a href="{$WWWROOT}view/view.php?id={$view->id}">
				{$view->title}
				</a>
			</li>
			{/foreach}
      </ul>
    {/if}
    {if $user->introduction}{$user->introduction|str_shorten_html:100:true|safe}{/if}
    {if $user->pending}
      <div class="pending">
		<strong>
			{str tag='whymakemeyourfriend' section='group'}
		</strong>
		<span>{$user->message|format_whitespace|safe}</span>
      </div>
    {/if}
	</div>
</td>

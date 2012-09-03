<td class="friendinfo{if $user->pending} pending rel{/if}">
<div class="peoplelistinfo">
    <div class="leftdiv" id="friendinfo_{$user->id}">
          <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="">
    </div>
 
    <div class="rightdiv">
        <h4><a href="{profile_url($user)}">{$user->display_name}</a>
        {if $user->pending}
          <span class="pendingfriend"> - {str tag='pending' section='group'}</span>
        {elseif $user->friend && $page == 'find'}
          <span class="existingfriend"> - {str tag='existingfriend' section='group'}</span>
        {/if}
        </h4>
      {if $user->introduction}<div class="userintro">{$user->introduction|str_shorten_html:100:true|safe}</div>{/if}
      {if $user->friend && $page == 'myfriends' && $user->views}
        <ul class="viewlist">
          <li class="label">
            <strong>{str tag='Views' section='group'}</strong>
          </li>
          {foreach from=$user->views item=view}
          <li>
            <a href="{$view->fullurl}">{$view->title}</a>
          </li>
          {/foreach}
        </ul>
      {/if}
      {if $user->pending}
        <div class="btn-pending s">
          <label>
            {str tag='whymakemeyourfriend' section='group'}
          </label>
          <span>{$user->message|format_whitespace|safe}</span>
        </div>
      {/if}
    </div>
  </div>
</td>
<td class="friendinfo{if $user->pending} pending rel{/if} actionlisttd">
	<ul class="actionlist">
      {if $user->institutions}<li>{$user->institutions|safe}</li>{/if}
      {if $user->pending}
		<li class="approvefriend">{$user->accept|safe}</li>
		<li>
			<a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}" class="btn-deny">
				{str tag='denyrequest' section='group'}
			</a>
		</li>
      {/if}
      {if $user->messages}
		<li>
			<a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}" class="btn-message">
				{str tag='sendmessage' section='group'}
			</a>
		</li>
      {/if}
      {if $user->friend}
		<li>
			<a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}" class="btn-del">
				{str tag='removefromfriendslist' section='group'}
			</a>
		</li>
      {elseif $user->requestedfriendship}
		<li>
			<i>{str tag='friendshiprequested' section='group'}</i>
		</li>
      {elseif !$user->pending} {* Not an existing, pending, or requested friend *}
		<li class="friend">
			{if $user->friendscontrol == 'auth'}
			<a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}" class="btn-request">
				{str tag='sendfriendrequest' section='group'}
			</a>
			{elseif $user->friendscontrol == 'auto'}
				{$user->makefriend|safe}
			{else}
				{str tag='userdoesntwantfriends' section='group'}
			{/if}
		</li>
      {/if}
      {if $admingroups}
      <li><a href="" onclick="showGroupBox(event, {$user->id})" class="btn-edit">{str tag='editgroupmembership' section='group'}</a></li>
      {/if}
	</ul>

</td>

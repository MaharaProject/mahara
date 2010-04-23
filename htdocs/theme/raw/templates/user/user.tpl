{if $user->pending}
<td class="pending friendinfo rel">
	<ul class="actionlist">
        {if $user->institutions}<li>{$user->institutions|escape}</li>{/if}
		<li>{$user->accept}</li>
		<li>
			<a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}" id="btn-denyrequest">
				{str tag='denyrequest' section='group'}
			</a>
		</li>
		{if $user->messages}
		<li {if !$admingroups} class="last"{/if}>
			<a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}" id="btn-sendmessage">
				{str tag='sendmessage' section='group'}
			</a>
		</li>
		{/if}
                {if $admingroups->controlled}
                <li {if !$admingroups->invite}class="last"{/if}><a href="#" onclick="showGroupBox(event, {$user->id}, 'controlled')">{str tag='controlledmembership' section='group'}</a></li>
                <div class="groupbox" id="controlledgroupbox_{$user->id}">
                    <ul></ul>
                </div>
                {/if}
                {if $admingroups->invite}
                <li class="last"><a href="#" onclick="showGroupBox(event, {$user->id}, 'invite')">{str tag='invitemembership' section='group'}</a></li>
                <div class="groupbox" id="invitegroupbox_{$user->id}">
                    <ul></ul>
                </div>
                {/if}
	</ul>
	<div class="leftdiv" id="friendinfo_{$user->id}">
        <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="">
	</div>
	<div class="rightdiv">
	<h3>
		<a href="{$WWWROOT}user/view.php?id={$user->id}">
			{$user->display_name|escape}
		</a>
		- {str tag='pending' section='group'}
	</h3>
	{if $user->introduction}
		{$user->introduction|clean_html}
	{/if}
	<div class="pending">
		<strong>
			{str tag='whymakemeyourfriend' section='group'}
		</strong> 
		<span>{$user->message|format_whitespace}</span>
	</div>
	</div>
</td>
{elseif $user->friend}
<td class="friendinfo">
	<ul class="actionlist">
    {if $user->institutions}<li>{$user->institutions|escape}</li>{/if}
	{if $user->messages}
		<li>
			<a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}" id="btn-sendmessage">
				{str tag='sendmessage' section='group'}
			</a>
		</li>
	{/if}
		<li {if !$admingroups} class="last"{/if}>
			<a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}" id="btn-delete">
				{str tag='removefromfriendslist' section='group'}
			</a>
		</li>
                {if $admingroups->controlled}
                <li {if !$admingroups->invite}class="last"{/if}><a href="#" onclick="showGroupBox(event, {$user->id}, 'controlled')">{str tag='controlledmembership' section='group'}</a></li>
                <div class="groupbox" id="controlledgroupbox_{$user->id}">
                    <ul></ul>
                </div>
                {/if}
                {if $admingroups->invite}
                <li class="last"><a href="#" onclick="showGroupBox(event, {$user->id}, 'invite')">{str tag='invitemembership' section='group'}</a></li>
                <div class="groupbox" id="invitegroupbox_{$user->id}">
                    <ul></ul>
                </div>
                {/if}
	</ul>
	<div class="leftdiv" id="friendinfo_{$user->id}">
        <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="">
	</div>
	<div class="rightdiv">
	<h3>
		<a href="{$WWWROOT}user/view.php?id={$user->id}">
			{$user->display_name|escape}
		</a>
		{if $page == 'find'}
			- {str tag='existingfriend' section='group'}
		{/if}
	</h3>
	{if $page == 'myfriends'}
		{if $user->views}
		<ul class="viewlist">
			<li class="label">
				<strong>{str tag='Views' section='group'}</strong>
			</li>
			{foreach from=$user->views item=view}
			<li>
				<a href="{$WWWROOT}view/view.php?id={$view->id}">
				{$view->title|escape}
				</a>
			</li>
			{/foreach}
		</ul>
		{/if}
	{/if}
	{if $user->introduction}
	<p>
		{$user->introduction}
	</p>
	{/if}
	</div>
</td>
{elseif $user->requestedfriendship}
<td class="friendinfo">
	<ul class="actionlist">
        {if $user->institutions}<li>{$user->institutions|escape}</li>{/if}
		{if $user->messages}
		<li>
			<a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}" id="btn-sendmessage">
				{str tag='sendmessage' section='group'}
			</a>
		</li>
		{/if}
		<li {if !$admingroups} class="last"{/if}>
			{str tag='friendshiprequested' section='group'}
		</li>
                {if $admingroups->controlled}
                <li {if !$admingroups->invite}class="last"{/if}><a href="#" onclick="showGroupBox(event, {$user->id}, 'controlled')">{str tag='controlledmembership' section='group'}</a></li>
                <div class="groupbox" id="controlledgroupbox_{$user->id}">
                    <ul></ul>
                </div>
                {/if}
                {if $admingroups->invite}
                <li class="last"><a href="#" onclick="showGroupBox(event, {$user->id}, 'invite')">{str tag='invitemembership' section='group'}</a></li>
                <div class="groupbox" id="invitegroupbox_{$user->id}">
                    <ul></ul>
                </div>
                {/if}
	</ul>
	<div class="leftdiv" id="friendinfo_{$user->id}">
        <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="">
	</div>
	<div class="rightdiv">
	<h3>
		<a href="{$WWWROOT}user/view.php?id={$user->id}">
			{$user->display_name|escape}
		</a>
	</h3>
	{if $user->introduction}
		{$user->introduction}
	{/if}
	</div>
</td>
{else}
<td class="friendinfo">
	<ul class="actionlist">
        {if $user->institutions}<li>{$user->institutions|escape}</li>{/if}
		{if $user->messages}
		<li>
			<a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}" id="btn-sendmessage">
				{str tag='sendmessage' section='group'}
			</a>
		</li>
		{/if}
		<li {if !$admingroups} class="last"{/if}>
			{if $user->friendscontrol == 'auth'}
			<a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}" id="btn-request">
				{str tag='sendfriendrequest' section='group'}
			</a>
			{elseif $user->friendscontrol == 'auto'}
				{$user->makefriend}
			{else}
				{str tag='userdoesntwantfriends' section='group'}
			{/if}
		</li>
                {if $admingroups->controlled}
                <li {if !$admingroups->invite}class="last"{/if}><a href="#" onclick="showGroupBox(event, {$user->id}, 'controlled')">{str tag='controlledmembership' section='group'}</a></li>
                <div class="groupbox" id="controlledgroupbox_{$user->id}">
                    <ul></ul>
                </div>
                {/if}
                {if $admingroups->invite}
                <li class="last"><a href="#" onclick="showGroupBox(event, {$user->id}, 'invite')">{str tag='invitemembership' section='group'}</a></li>
                <div class="groupbox" id="invitegroupbox_{$user->id}">
                    <ul></ul>
                </div>
                {/if}
	</ul>
    <div class="leftdiv" id="friendinfo_{$user->id}">
        <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="">
    </div>
	<div class="rightdiv">
	<h3>
		<a href="{$WWWROOT}user/view.php?id={$user->id}">
			{$user->display_name|escape}
		</a>
	</h3>
	{if $user->introduction}
		{$user->introduction}
	{/if}
	</div>
</td>
{/if}

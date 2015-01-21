<div class="listrow {if $user->pending} pending{/if}">
  <div class="peoplelistinfo">
    <div class="pull-left" id="friendinfo_{$user->id}">
          <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
    </div>

    <div class="">
        <h3 class="title pull-left"><a href="{profile_url($user)}">{$user->display_name}</a>
        {if $user->pending}
          <i><span class="pendingfriend"> - {str tag='pending' section='group'}</span></i>
        {elseif $user->friend && $page == 'find'}
          <i><span class="existingfriend"> - {str tag='existingfriend' section='group'}</span></i>
        {/if}
        </h3>

    <ul class="actionlist pull-right list-unstyled">
      {if $user->institutions}<li class="notbtn">{$user->institutions|safe}</li>{/if}
      {if $user->pending}
        <li class="approvefriend">{$user->accept|safe}</li>
        <li class="denyrequest">
            <span class="glyphicon glyphicon-ban-circle"></span>
            <a href="{$WWWROOT}user/denyrequest.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-deny">
                {str tag='denyrequest' section='group'}
            </a>
        </li>
      {/if}
      {if $user->friend}
        <li class="removefriend">
            <span class="glyphicon glyphicon-remove"></span>
            <a href="{$WWWROOT}user/removefriend.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-del">
                {str tag='removefromfriendslist' section='group'}
            </a>
        </li>
      {elseif $user->requestedfriendship}
        <li class="notbtn">
            <span class="btn">{str tag='friendshiprequested' section='group'}</span>
        </li>
      {elseif !$user->pending} {* Not an existing, pending, or requested friend *}
        {if $user->friendscontrol == 'auth'}
        <li class="friend">
            <span class="glyphicon glyphicon-user"></span>
            <a href="{$WWWROOT}user/requestfriendship.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-request">
                {str tag='sendfriendrequest' section='group'}
            </a>
        </li>
        {elseif $user->friendscontrol == 'auto'}
        <li class="friend">
            {$user->makefriend|safe}
        </li>
        {else}
        <li class="nofriend">
            {str tag='userdoesntwantfriends' section='group'}
        </li>
        {/if}
      {/if}
      {if $user->messages}
        <li class="messages">
            <span class="glyphicon glyphicon-envelope"></span>
            <a href="{$WWWROOT}user/sendmessage.php?id={$user->id}&amp;returnto={$page}&amp;offset={$offset}" class="btn-message">
                {str tag='sendmessage' section='group'}
            </a>
        </li>
      {/if}
      {if $admingroups}
      <li class="editgroup">
        <span class="glyphicon glyphicon-pencil"></span>
        <a href="" onclick="showGroupBox(event, {$user->id})" class="btn-edit">{str tag='editgroupmembership' section='group'}</a>
      </li>
      {/if}
    </ul>

      {if $user->introduction}<div class="detail">{$user->introduction|str_shorten_html:100:true|safe}</div>{/if}
      {if $user->friend && $page == 'myfriends' && $user->views}
        <ul class="viewlist">
          <li>
            <strong>{str tag='Views' section='group'}:</strong>
          </li>
          {foreach from=$user->views item=view}
          <li>
            <a href="{$view->fullurl}">{$view->title}</a>
          </li>
          {/foreach}
        </ul>
      {/if}
      {if $user->pending}
        <div class="whymakemeyourfriend">
          <strong>
            {str tag='whymakemeyourfriend' section='group'}
          </strong>
          <span>{$user->message|format_whitespace|safe}</span>
        </div>
      {/if}
    </div>
  </div>
  <div class="cb"></div>
</div>

{include file="header.tpl"}

{if $messages}
<div id="messagethread" class="fullwidth fixwidth listing table">
    {foreach from=$messages item=message}
        <div class="{cycle values='r0,r1'} listrow">
          <div class="fl membericon">
            <img src="{profile_icon_url user=$message->from maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
          </div>
          <div class="memberdetail">
            <h3 class="title">
        {if $message->from == $user->id}
              <a href="{profile_url($user)}">{$user|display_name}</a>
        {else}
              <a href="{profile_url($USER)}">{$USER|display_name}</a>
        {/if}
              <span class="postedon">{$message->ctime|strtotime|format_date}</span>
            </h3>
            <div class="detail messagebody">{$message->message}</div>
          </div>
        </div>
    {/foreach}
</div>
{/if}

{$form|safe}

{include file="footer.tpl"}

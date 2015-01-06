{if $user->deleted}
<div class="peoplelistinfo">
    <div class="leftdiv profile"></div>
    <div class="rightdiv">
        <h3>{display_name $user}</h3>
    </div>
    <div class="cb"></div>
</div>
{else}
<div class="peoplelistinfo">
    <div class="leftdiv profile" id="{$type}_{$user->id}"><img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}"></div>
    <div class="rightdiv">
        <h3 class="title"><a href="{profile_url($user)}">{$user->display_name}</a></h3>
          {if $user->introduction}
        <div class="detail">{$user->introduction|str_shorten_html:140:true|safe}</div>
        {/if}
    </div>
    <div class="cb"></div>
</div>
{/if}
{if $user->deleted}
<div class="peoplelistinfo">
    <h3 class="list-group-item-heading text-inline">{display_name $user}</h3>
</div>
{else}
<div class="peoplelistinfo">
    <div class="profile" id="{$type}_{$user->id}">
        <img src="{profile_icon_url user=$user maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
        <h3 class="title list-group-item-heading text-inline">
            <a href="{profile_url($user)}">{$user->display_name}</a>
        </h3>
        {if $user->introduction}
        <div class="detail">{$user->introduction|str_shorten_html:140:true|safe}</div>
        {/if}
    </div>
</div>
{/if}
<div>
    <div class="fl">
        <img src="{profile_icon_url user=$user maxwidth=50 maxheight=50}" alt="">
    </div>
    <h3><a href="{profile_url($user)}">{$user|display_name}</a></h3>
    {if $user->introduction}
    <p>{$user->introduction|clean_html|safe}</p>
    {else}
    <br><br>
    {/if}
</div>

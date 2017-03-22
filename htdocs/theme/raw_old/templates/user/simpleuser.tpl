<h3 class="user-icon-name">
    <span class="user-icon pull-left">
        <img src="{profile_icon_url user=$user maxwidth=50 maxheight=50}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
    </span>
    <a href="{profile_url($user)}" class="media-right">
        {$user|display_name}
    </a>
</h3>
<div class="clearfix view-container">
    {if $user->introduction}
    <p>
        {$user->introduction|clean_html|safe}
    </p>
    {/if}
</div>

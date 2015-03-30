<div>
    <h3 class="user-icon-name">
        <span class="usericon">
            <img src="{profile_icon_url user=$user maxwidth=50 maxheight=50}" alt="{str tag=profileimagetext arg1=$user|display_default_name}">
        </span>
        <a href="{profile_url($user)}">
            {$user|display_name}
        </a>
    </h3>
    {if $user->introduction}
    <p class="ptl">
        {$user->introduction|clean_html|safe}
    </p>
    {/if}
</div>

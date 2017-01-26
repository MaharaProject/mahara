<div class="js-masonry user-thumbnails">
{foreach from=$friends item=row}
    {foreach from=$row item=friend}
        <a href="{profile_url($friend)}" class="item user-icon metadata user-icon-larger {cycle values='d0,d1'}">
            <img src="{profile_icon_url user=$friend maxwidth=100 maxheight=100}" alt="{str tag=profileimagetext arg1=$friend|display_default_name}" with="100" height="100">
            <p class="member-name">{$friend|display_default_name|escape}</p>
        </a>
    {/foreach}
{/foreach}
</div>


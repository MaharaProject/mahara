<div class="friends">
    <div class="js-masonry user-thumbnails">
        {foreach from=$groupmembers item=groupmember}
            <a href="{profile_url($groupmember)}" class="item user-icon metadata user-icon-100">
                <img src="{profile_icon_url user=$groupmember maxwidth=100 maxheight=100}" alt="{str tag=profileimagetext arg1=$groupmember|display_default_name}" title="{$groupmember|display_default_name|escape}">
                 <p class="member-name">{$groupmember|display_default_name}</p>
            </a>
        {/foreach}
    </div>
</div>

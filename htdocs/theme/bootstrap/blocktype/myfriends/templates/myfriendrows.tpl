    {foreach from=$friends item=row}
        {foreach from=$row item=friend}
            <div class="{cycle values='d0,d1'} center friendcell">
                <a href="{profile_url($friend)}">
                   <img src="{profile_icon_url user=$friend maxwidth=60 maxheight=60}" alt="{str tag=profileimagetext arg1=$friend|display_default_name}">
                </a>
                <div class="membername"><a href="{profile_url($friend)}">{$friend|display_default_name|escape}</a></div>
            </div>
        {/foreach}
    {/foreach}
    <div class="cl"></div>

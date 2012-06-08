    {foreach from=$friends item=row}
        {foreach from=$row item=friend}
            <div class="{cycle values='d0,d1'} friendcell">
                <a href="{profile_url($friend)}">
                   <img src="{profile_icon_url user=$friend maxwidth=60 maxheight=60}" alt="">
                </a>
                <div><a href="{profile_url($friend)}">{$friend|display_default_name|escape}</a></div>
            </div>
        {/foreach}
    {/foreach}
    <div class="cl"></div>

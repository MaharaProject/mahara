    {foreach from=$friends item=row}
        {foreach from=$row item=friend}
            <div class="{cycle values='d0,d1'} friendcell">
                <a href="{$WWWROOT}user/view.php?id={$friend->id}">
                   <img src="{profile_icon_url user=$friend maxwidth=60 maxheight=60}" alt="">
                </a>
                <div><a href="{$WWWROOT}user/view.php?id={$friend->id}">{$friend|display_default_name|escape}</a></div>
            </div>
        {/foreach}
    {/foreach}

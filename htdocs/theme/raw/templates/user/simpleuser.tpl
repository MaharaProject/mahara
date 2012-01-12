<div>
    <div class="fl">
        <img src="{$WWWROOT}thumb.php?type=profileiconbyid&amp;maxwidth=50&amp;maxheight=50&amp;id={$user->profileicon}" alt="">
    </div>
    <h3><a href="{$WWWROOT}user/view.php?id={$user->id}">{$user|display_name}</a></h3>
    {if $user->introduction}
    <p>{$user->introduction|clean_html|safe}</p>
    {else}
    <br><br>
    {/if}
</div>

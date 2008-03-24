<div id="wall">
    {if $wallmessage}
        <p>{$wallmessage}</p>
    {/if}
    {if $wallposts}
        {foreach from=$wallposts item=wallpost}
            <div id="wallpost">
                <div id="icon"><img src="{$WWWROOT}/thumb.php?type=profileicon&maxwidth=50&maxheight=50&id={$wallpost.from}" /></div>
                <div id="text">{$wallpost.text|h}</div>
                <div id="postedon">{$wallpost.postdate|format_date}</div>
                <div id="controls">controls</div>
            </div>
        {/foreach}
    {/if}
</div>

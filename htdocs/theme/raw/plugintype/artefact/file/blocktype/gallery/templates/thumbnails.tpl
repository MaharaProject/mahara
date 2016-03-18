<div id="thumbnails{$instanceid}" class="panel-body thumbnails js-masonry">
    {foreach from=$images item=image}
        <div style="width: {$width * 1.5}px;" class="thumb">
        <a rel="{$image.slimbox2}" href="{$image.link}" title="{$image.title}">
            <img src="{$image.source}" {if $image.height}height="{$image.height}"{/if} alt="{$image.title}" title="{$image.title}" {if $frame}class="frame center-block"{/if} />
        </a>
        {if $showdescription && $image.title}
            <p class="text-small title">
                {$image.title|truncate:60|safe}
            </p>
        {/if}
        </div>
    {/foreach}
</div>

{if isset($copyright)}
<div id="lbBottom" class="license">
    {$copyright|safe}
</div>
{/if}

{$comments|safe}

<div id="thumbnails{$instanceid}" class="card-body thumbnails js-masonry">
    {foreach from=$images item=image}
        <div style="width: {$width * 1.5}px;" class="thumb">
        <a data-fancybox="{$image.fancybox}" href="{$image.link}" title="{$image.title}" data-caption="{$image.title}">
            <img src="{$image.source}" {if $image.height}height="{$image.height}"{/if} alt="{$image.title}" title="{$image.title}" {if $frame}class="frame mx-auto d-block"{/if} />
        </a>
        {if $showdescription && $image.title}
            <p class="text-small title">
                {$image.title|truncate:60|safe|clean_html}
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

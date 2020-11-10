<div id="thumbnails{$instanceid}" class="card-body thumbnails js-masonry">
    {foreach from=$images item=image}
        <div {if $image.squaredimensions}style="width:{$image.squaredimensions}px;height:{$image.squaredimensions}px;"{/if} class="thumb">
            <a data-fancybox="{$image.fancybox}" href="{$image.link}" title="{$image.title}" data-caption="{$image.title}">
                <img src="{$image.source}" alt="{$image.title}" title="{$image.title}" width="{$width}" height="{$width}"/>
            </a>
        {if $showdescription && $image.title}
        <p class="text-small title">
            {$image.title|truncate:60|clean_html|safe}
        </p>
        {/if}
        </div>
    {/foreach}
</div>
{if isset($copyright)}
<div id="lbBottom">
    {$copyright|safe}
</div>
{/if}

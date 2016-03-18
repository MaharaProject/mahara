<div id="thumbnails{$instanceid}" class="panel-body thumbnails js-masonry">
    {foreach from=$images item=image}
        <div {if $image.squaredimensions}style="width:{$image.squaredimensions}px;height:{$image.squaredimensions}px;"{/if} class="thumb">
            <a rel="{$image.slimbox2}" href="{$image.link}" title="{$image.title}">
                <img src="{$image.source}" alt="{$image.title}" title="{$image.title}" width="{if $image.width}{$image.width}{else}{$width}{/if}" height="{if $image.height}{$image.height}{else}{$width}{/if}" {if $frame}class="frame center-block"{/if}/>
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
<div id="lbBottom">
    {$copyright|safe}
</div>
{/if}

{$comments|safe}

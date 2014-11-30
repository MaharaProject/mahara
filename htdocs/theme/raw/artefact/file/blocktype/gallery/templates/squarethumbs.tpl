<div class="thumbnails" id="thumbnails{$instanceid}">
    {foreach from=$images item=image}
        <div style="float:left;{if $frame} padding: 3px;{/if}{if $image.squaredimensions}width:{$image.squaredimensions}px;height:{$image.squaredimensions}px;{/if}" class="thumb">
         <div class="thumbimage" style="{if $image.squaredimensions}height:{$image.squaredimensions}px;{/if}">
        <a rel="{$image.slimbox2}" href="{$image.link}" title="{$image.title}" style="position:relative;top:{$image.squaretop}px" target="_blank">
            <img src="{$image.source}" alt="{$image.title}" title="{$image.title}" width="{if $image.width}{$image.width}{else}{$width}{/if}" height="{if $image.height}{$image.height}{else}{$width}{/if}" {if $frame}class="frame"{/if}/>
        </a>
         </div>
         {if $showdescription && $image.title}<div class="caption" style="top: 4px;width: {$width}px;">{$image.title|safe}</div>{/if}
        </div>
    {/foreach}
    <div class="cb"></div>
</div>
<script type="application/javascript">
$j(function() {
    if ($j('#thumbnails{$instanceid}')) {
        // adjust height of image + description box to align things up
        var maxHeight = Math.max.apply(null, $j('#thumbnails{$instanceid} .thumb').map(function() {
            var height = parseInt($j(this).css('height'), 10);
            if ($j(this).find('.caption').length > 0) {
                height += parseInt($j(this).find('.caption').height(), 10);
            }
            return (height + 3); // we will give it a little more heigth to avoid a vertical scrollbar sometimes appearing
        }).get());
        $j('#thumbnails{$instanceid} .thumb').each(function() {
            $j(this).css('height', maxHeight);
        });
    }
});
</script>
{if isset($copyright)}<div class="cb" id="lbBottom">{$copyright|safe}</div>{/if}
{if $commentcount || $commentcount === '0'}
{$comments|safe}
{/if}

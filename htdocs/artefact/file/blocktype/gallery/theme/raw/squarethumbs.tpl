<div class="thumbnails">
    {foreach from=$images item=image}
        <span style="float:left;{if $frame} padding:0.3em;{/if}">
        <a rel="{$image.slimbox2}" href="{$image.link}" title="{$image.title}" target="_blank">
            <img src="{$image.source}" alt="{$image.title}" title="{$image.title}" width="{$width}" height="{$width}" {if $frame}class="frame"{/if}/>
        </a>
        </span>
    {/foreach}
</div>
{if isset($copyright)}<div class="cb" id="lbBottom">{$copyright|safe}</div>{/if}

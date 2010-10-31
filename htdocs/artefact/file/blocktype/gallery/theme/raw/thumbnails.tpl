<div class="thumbnails">
    {foreach from=$images item=image}
        <a href="{$image.link}" target="_blank">
            <img src="{$image.source}" alt="{$image.title}" title="{$image.title}" />
        </a>
    {/foreach}
</div>

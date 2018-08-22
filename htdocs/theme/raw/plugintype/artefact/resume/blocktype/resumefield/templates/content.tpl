{if $editing && $nodata}
    <p class="editor-description">{$nodata}</p>
{else}
    {$content|safe}
{/if}

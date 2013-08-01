{if $attachments}
<hr>
<div class="attachments">
    <div class="attachmessage">{$attachmsgstr}</div>
    <span class="composite-attachments">
        <strong>{$attachmentsstr}</strong>
        {foreach from=$attachments item=item name=list implode=", "}
            <a href="{$WWWROOT}artefact/file/index.php?folder={$folderid}">{$item->title}</a>
        {/foreach}
    </span>
</div>
{/if}
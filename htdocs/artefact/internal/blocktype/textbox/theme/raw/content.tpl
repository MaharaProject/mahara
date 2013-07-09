{$text|clean_html|safe}
{if $commentcount || $commentcount === 0}
<div class="comments">
  <a href="{$artefacturl}">{str tag=Comments section=artefact.comment} ({$commentcount})</a>
</div>
{/if}


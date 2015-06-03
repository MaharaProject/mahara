{if $allowcomments}
<div class="comments mbl ptm pbl">
    <a class="commentlink text-thin pull-left" id="block_{$blockid}" href="{$artefacturl}">
        {str tag=Comments section=artefact.comment} ({$commentcount})
    </a>
</div>
{/if}
{if !$editing}
<div class="feedbacktablewrapper">
    <div id="feedbacktable_{$blockid}" class="feedbacktable">
        {$comments->tablerows|safe}
    </div>
</div>
{/if}
<div class="comments">
    {if $commentcount > 0}
        <a class="commentlink" id="block_{$blockid}" href="{$artefacturl}">{str tag=Comments section=artefact.comment} ({$commentcount})</a>
    {else}
        {if $allowcomments}
            <span id='block_{$blockid}' class="nocomments">{str tag=Comments section=artefact.comment} ({$commentcount})</span>
        {/if}
    {/if}
    {if $allowcomments}
        <a class="addcomment bar-before" href="{$artefacturl}">{str tag=addcomment section=artefact.comment}</a>
    {/if}
</div>
<div class="feedbacktablewrapper">
    <div id="feedbacktable_{$blockid}" class="feedbacktable">
        {$comments->tablerows|safe}
    </div>
</div>
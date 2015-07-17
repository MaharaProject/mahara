{if $editing}
<div class="panel-body">
    <p class="metadata">{$editing}</p>
</div>
{elseif $feedback}
    <div id="feedbacktable" class="feedbacktable feedbackblock fullwidth">
        {$feedback->tablerows|safe}
    </div>
    {$feedback->pagination|safe}
    {if $enablecomments}
    <a id="add_feedback_link" class="feedback" href="#" data-toggle="modal-docked" data-target="#feedback-form">
        {str tag=addcomment section=artefact.comment}
    </a>
    {/if}
{/if}

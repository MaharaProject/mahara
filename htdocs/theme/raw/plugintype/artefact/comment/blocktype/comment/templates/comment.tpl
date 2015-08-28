{if $editing}
<div class="panel-body">
    <p class="metadata">{$editing}</p>
</div>
{elseif $feedback}
    {* Do not change the id because it is used by paginator.js *}
    <div id="feedbacktable" class="feedbacktable feedbackblock fullwidth">
        {$feedback->tablerows|safe}
    </div>
    {$feedback->pagination|safe}
    {if $enablecomments}
    <a id="add_feedback_link" class="feedback link-blocktype last" href="#" data-toggle="modal-docked" data-target="#feedback-form">
        <span class="icon icon-plus"></span>
        {str tag=addcomment section=artefact.comment}
    </a>
    {/if}
{/if}

{if $editing}
<div class="panel-body">
    <p class="alert alert-info">{$editing}</p>
</div>
{elseif $feedback}
    {* Do not change the id because it is used by paginator.js *}
    <div id="feedbacktable" class="feedbacktable js-feedbackblock fullwidth">
        {$feedback->tablerows|safe}
    </div>
    {$feedback->pagination|safe}
    {if $enablecomments}
    <a id="add_feedback_link" class="js-add-comment-modal feedback link-blocktype" href="#" data-toggle="modal-docked" data-target="#feedback-form">
        <span class="icon icon-plus" role="presentation" aria-hidden="true"></span>
        {str tag=addcomment section=artefact.comment}
    </a>
    {/if}
{/if}

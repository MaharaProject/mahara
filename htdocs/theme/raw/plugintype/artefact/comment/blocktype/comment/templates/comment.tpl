{if $editing}
<div class="card-body">
    <p class="alert alert-info">{$editing}</p>
</div>
{elseif $feedback}
    {* Do not change the id because it is used by paginator.js *}
    <div id="feedbacktable{if $blockid}_{$blockid}{/if}" class="feedbacktable js-feedbackblock fullwidth">
        {$feedback->tablerows|safe}
    </div>
    <p>
        {$feedback->pagination|safe}
        {if $feedback->pagination_js}
            <script>
                {$feedback->pagination_js|safe}
            </script>
        {/if}
    </p>
    {if $enablecomments}
    <a id="add_feedback_link" class="js-add-comment-modal feedback link-blocktype" href="#" data-toggle="modal-docked" data-target="#feedback-form">
        <span class="icon icon-plus" role="presentation" aria-hidden="true"></span>
        {str tag=addcomment section=artefact.comment}
    </a>
    {/if}
{/if}

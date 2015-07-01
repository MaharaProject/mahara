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
    <div class="text-right">
        <a id="add_feedback_link" class="feedback btn btn-default btn-sm" href="#" data-toggle="modal" data-target="#feedback-form">
            <span class="icon icon-lg icon-plus prm"></span>
            {str tag=addcomment section=artefact.comment}
        </a>
    </div>
    {/if}
{/if}

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
        <a id="add_feedback_link" class="feedback" href="">{str tag=placefeedback section=artefact.comment}</a>
        <script type="application/javascript">
            var feedbacklinkinblock = true;
        </script>
    {/if}
{/if}
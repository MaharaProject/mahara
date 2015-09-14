{if $enablecomments || $LOGGEDIN}
<div id="add-comment" class="add-comment-container ptxl pbl">
    {if $enablecomments}
    <h3 id="add_feedback_heading">
        <span class="icon icon-comments prm"></span>
        {str tag=addcomment section=artefact.comment}
    </h3>
    {/if}

    <div class="addcommentform" id="comment-form">
        {$addfeedbackform|safe}
    </div>
</div>
{/if}
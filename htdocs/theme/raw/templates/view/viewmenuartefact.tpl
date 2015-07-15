{if $enablecomments || $LOGGEDIN}
    {if $enablecomments}
    <ul class="nav nav-tabs" role="tablist">
        <li id="add_feedback_link" class="feedback active" role="presentation">
            <a href="#comment-form" aria-controls="comment-form" role="tab" data-toggle="tab">
                <span class="icon icon-lg icon-comments prm"></span>
                {str tag=addcomment section=artefact.comment}
            </a>
        </li>
    </ul>
    {/if}
    <div class="tab-panel">
        <div role="tabpanel" class="tab-pane active" id="comment-form">
            {$addfeedbackform|safe}
        </div>
    </div>
{/if}

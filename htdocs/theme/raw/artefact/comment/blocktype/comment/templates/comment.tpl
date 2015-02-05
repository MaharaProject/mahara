{if $feedback}
<div class="viewfooter">
    <table id="feedbacktable" class="feedbacktable fullwidth table">
        <tbody>
        {$feedback->tablerows|safe}
        </tbody>
    </table>
    {$feedback->pagination|safe}
    {if $enablecomments}
        <a id="add_feedback_link" class="feedback" href="">{str tag=placefeedback section=artefact.comment}</a>
        <script type="application/javascript">
            var feedbacklinkinblock = true;
        </script>
    {/if}
</div>
{/if}
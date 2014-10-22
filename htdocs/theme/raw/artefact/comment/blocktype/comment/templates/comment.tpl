{if $feedback}
<div class="viewfooter">
    <table id="feedbacktable" class="fullwidth table">
        <tbody>
        {$feedback->tablerows|safe}
        </tbody>
    </table>
    {$feedback->pagination|safe}
    {if $enablecomments}
        <a id="add_feedback_link" class="feedback" href="">{str tag=placefeedback section=artefact.comment}</a>
        <script type="text/javascript">
            var feedbacklinkinblock = true;
        </script>
    {/if}
</div>
{/if}
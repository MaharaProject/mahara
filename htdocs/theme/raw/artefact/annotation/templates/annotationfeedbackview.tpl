<div id="annotationfeedbackview_{$blockid}">
    <div class="annotationfeedback">
        {if $annotationfeedbackcount > 0}
            {if !$editing}
            <a class="annotationfeedbacklink" id="annotation_feedback_link_{$blockid}" href="{$artefacturl}">
            {/if}
            {str tag=Annotationfeedback section=artefact.annotation} ({$annotationfeedbackcount})
            {if !$editing}</a>{/if}
        {else}
            {if $allowfeedback}
                <span id='annotation_feedback_link_{$blockid}' class="noannotationfeedback">{str tag=Annotationfeedback section=artefact.annotation} ({$annotationfeedbackcount})</span>
            {/if}
        {/if}
        {if $allowfeedback && !$editing}
            <a id="add_annotation_feedback_link_{$blockid}" class="placeannotationfeedback bar-before" href="">{str tag=placeannotationfeedback section=artefact.annotation}</a>
            {$addannotationfeedbackform|safe}
            <script type="text/javascript">
                var annotationfeedbacklinkinblock = true;
            </script>
        {/if}
    </div>
    {if !$editing}
    <div class="annotationfeedbacktablewrapper">
        <div id="annotationfeedbacktable_{$blockid}" class="annotationfeedbacktable">
            {$annotationfeedback->tablerows|safe}
        </div>
    </div>
    {/if}
</div>
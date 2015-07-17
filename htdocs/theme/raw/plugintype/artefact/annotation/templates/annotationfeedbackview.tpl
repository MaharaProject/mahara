<div id="annotationfeedbackview_{$blockid}" class="annotation-feedback">
    {if $annotationfeedback}
    <div class="annotationfeedback">
        {if $annotationfeedbackcount > 0}
        <a id="feedback_{$blockid}" class="placeannotationfeedback" data-toggle="modal-docked" data-target="#annotation_feedback_{$blockid}" href="#">
            {str tag=Annotationfeedback section=artefact.annotation} ({$annotationfeedbackcount})
        </a>
        {else}
            {if $allowfeedback && !$editing}
            <a id="feedback_{$blockid}" class="placeannotationfeedback" data-toggle="modal-docked" data-target="#annotation_feedback_{$blockid}" href="#">
                <span class="icon icon-plus text-success prs"></span>
                {str tag=placeannotationfeedback section=artefact.annotation}
            </a>
            {/if}
        {/if}
    </div>
    {/if}

    {if !$editing}
    <div id="annotation_feedback_{$blockid}" class="feedbacktable modal modal-docked">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal-docked">
                        <span class="times">&times;</span>
                        <span class="sr-only">{str tag=Close}</span>
                    </button>
                    <h4 class="modal-title">
                        {if $annotationfeedbackcount > 0}
                            <span class="icon icon-lg icon-annotation"></span>
                            {str tag=Annotationfeedback section=artefact.annotation}
                        {else}
                            <span class="icon icon-lg icon-annotation"></span>
                            {str tag=placeannotationfeedback section=artefact.annotation}
                        {/if}
                    </h4>
                </div>
                <div class="modal-body">
                    {if $allowfeedback && !$editing}
                    <div id="add_annotation_feedback_{$blockid}" class="mbxl">
                        {$addannotationfeedbackform|safe}
                        <script type="application/javascript">
                            var annotationfeedbacklinkinblock = true;
                        </script>
                    </div>
                    {/if}
                    <hr />
                    <ul id="annotationfeedbacktable_{$blockid}"class="annotationfeedbacktable flush list-group list-group-lite list-unstyled">
                        {$annotationfeedback->tablerows|safe}
                    </ul>
                </div>
            </div>
        </div>
    </div>
    {/if}
</div>

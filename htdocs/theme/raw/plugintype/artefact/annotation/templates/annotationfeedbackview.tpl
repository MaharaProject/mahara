{if !$editing}
    <div id="annotationfeedbackview_{$blockid}" class="annotation-feedback">
        {if $annotationfeedback != 0}
            <ul id="annotationfeedbacktable_{$blockid}"class="annotationfeedbacktable flush list-group list-group-lite list-unstyled">
                {$annotationfeedback->tablerows|safe}
            </ul>
            {$annotationfeedback->pagination|safe}
        {/if}
        {if $allowfeedback}
        <div class="annotationfeedback">
            <a id="feedback_{$blockid}" class="placeannotationfeedback link-blocktype last" data-toggle="modal-docked" data-target="#annotation_feedback_{$blockid}" href="#">
                <span class="icon icon-plus" role="presentation" aria-hidden="true"></span>
                {str tag=placeannotationfeedback section=artefact.annotation}
            </a>
        </div>
        {/if}
        <div id="annotation_feedback_{$blockid}" class="feedbacktable modal modal-docked">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal-docked">
                            <span class="times">&times;</span>
                            <span class="sr-only">{str tag=Close}</span>
                        </button>
                        <h4 class="modal-title">
                            <span class="icon icon-lg icon-annotation" role="presentation" aria-hidden="true"></span>
                            {str tag=placeannotationfeedback section=artefact.annotation}
                        </h4>
                    </div>
                    <div class="modal-body">
                        {if $allowfeedback && !$editing}
                        <div id="add_annotation_feedback_{$blockid}">
                            {$addannotationfeedbackform|safe}
                        </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

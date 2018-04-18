{if !$editing}
    <div id="annotationfeedbackview_{$blockid}" class="annotation-feedback">
        {if $annotationfeedbackcount > 0}
            <a class="commentlink link-blocktype" id="block_{$blockid}" data-toggle="modal-docked" data-target="#annotation_feedbacktable_{$blockid}" href="#">
                <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
                {str tag=Annotationfeedback section=artefact.annotation} ({$annotationfeedbackcount})
            </a>
        {/if}
        {if $allowfeedback}
            <a id="feedback_{$blockid}" class="placeannotationfeedback link-blocktype last" data-toggle="modal-docked" data-target="#annotation_feedbackform_{$blockid}" href="#">
                <span class="icon icon-arrow-circle-right" role="presentation" aria-hidden="true"></span>
                {str tag=placeannotationfeedback section=artefact.annotation}
            </a>
        {/if}
    </div>
    <!-- modal for the feedback -->
    <div class="feedback modal modal-docked" id="annotation_feedbacktable_{$blockid}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header clearfix">
                    <button class="deletebutton close" data-dismiss="modal-docked">
                        <span class="times">&times;</span>
                        <span class="sr-only">{str tag=Close}</span>
                    </button>
                    <h4 class="modal-title float-left">
                        <span class="icon icon-lg icon-comments left" role="presentation" aria-hidden="true"></span>
                        {str tag=Annotationfeedback section=artefact.annotation} - {$annotationtitle}
                    </h4>
                </div>
                <div class="modal-body flush">
                    {$annotationfeedback->tablerows|safe}
                </div>
            </div>
        </div>
    </div>
    <!-- modal for the feedback form -->
    <div id="annotation_feedbackform_{$blockid}" class="feedbacktable modal modal-docked">
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

{/if}

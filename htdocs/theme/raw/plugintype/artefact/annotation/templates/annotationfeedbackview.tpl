<div id="annotationfeedbackview_{$blockid}" class="annotation-feedback">
    <div class="annotationfeedback collapsible-group">
        {if $allowfeedback && !$editing}
            <div class="panel panel-default collapsible first">
                <h4 class="panel-heading">
                     <a class="collapsed placeannotationfeedback" id="add_annotation_feedback_control_{$blockid}" href="#add_annotation_feedback_{$blockid}" data-toggle="collapse" aria-expanded="false" aria-controls="add_annotation_feedback_{$blockid}">

                        {str tag=placeannotationfeedback section=artefact.annotation}
                        <span class="icon icon-plus text-success pls"></span>
                        <span class="icon icon-chevron-down pls collapse-indicator pull-right"></span>
                    </a>
                </h4>
                <div class="panel-body collapse" id="add_annotation_feedback_{$blockid}">
                    {$addannotationfeedbackform|safe}
                    <script type="application/javascript">
                        var annotationfeedbacklinkinblock = true;
                    </script>
                </div>
            </div>
        {/if}

        {if $annotationfeedbackcount > 0}
        <div class="panel panel-default collapsible last">
            {if !$editing}
                <h4 class="panel-heading">
                    <a class="annotationfeedbacklink" id="annotationfeedbacktable_control_{$blockid}" href="#annotationfeedbacktable_{$blockid}" data-toggle="collapse" aria-expanded="false" aria-controls="annotationfeedbacktable_{$blockid}">
                        {str tag=Annotationfeedback section=artefact.annotation}
                        <span class="metadata">({$annotationfeedbackcount})</span>
                        <span class="icon icon-chevron-down pls collapse-indicator pull-right"></span>
                    </a>
                </h4>
            {else}
                {str tag=Annotationfeedback section=artefact.annotation} ({$annotationfeedbackcount})
            {/if}

            {if !$editing}
            <div class="panel-body no-footer p0 collapse in" id="annotationfeedbacktable_{$blockid}">
                <ul class="annotationfeedbacktable list-group list-group-lite list-unstyled">
                    {$annotationfeedback->tablerows|safe}
                </ul>
            </div>
            {/if}
        </div>
        {else}
            {if $allowfeedback}
                <span id='annotation_feedback_link_{$blockid}' class="noannotationfeedback">{str tag=Annotationfeedback section=artefact.annotation} ({$annotationfeedbackcount})</span>
            {/if}
        {/if}

    </div>
</div>

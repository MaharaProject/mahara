{if $noassessment && $editing}
    <p class="editor-description">{$noassessment}</p>
{else}
<div class="card-body">
    {if $allowfeedback}
        <a id="add_assessment_feedback_link" class="js-peerassessment-modal feedback link-blocktype" href="#" data-toggle="modal-docked" data-target="#assessment_feedbackform_{$blockid}" data-blockid="{$blockid}">
            <span class="icon icon-plus" role="presentation" aria-hidden="true"></span>
            {str tag=addpeerassessment section=blocktype.peerassessment/peerassessment}
        </a>
    {elseif $exporter}
        {if $instructions}
            <div>{str tag='instructions' section='view'}</div>
            <div class="viewinstruction-export">
                {$instructions|safe}
            </div>
        {/if}
    {/if}
</div>
{/if}
{if !$editing}
    {* Do not change the id because it is used by paginator.js *}
    <div id="assessmentfeedbacktable{$blockid}" class="feedbacktable js-feedbackblock fullwidth">
    {if $feedback}
        {$feedback->tablerows|safe}
        {$feedback->pagination|safe}
        {if $feedback->pagination_js}
        <script type="application/javascript">
            jQuery(function () {
                assessmentpaginator{$blockid} = {$feedback->pagination_js|safe}
            });
        </script>
        {/if}
    {/if}
    </div>

    <!-- modal for the peer assessment feedback -->
    <div id="assessment_feedbackform_{$blockid}" class="feedbacktable modal modal-docked">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal-docked">
                        <span class="times">&times;</span>
                        <span class="sr-only">{str tag=Close}</span>
                    </button>
                    <h4 class="modal-title">
                        <span class="icon icon-lg icon-peerassessment" role="presentation" aria-hidden="true"></span>
                        {str tag=title section=blocktype.peerassessment/peerassessment}
                    </h4>
                </div>
                <div class="modal-body">
                    {if $instructions}
                    <div class="last form-group collapsible-group small-group peerinstructions">
                        <fieldset class="pieform-fieldset collapsible collapsible-small">
                            <legend>
                                <h4>
                                    <a href="#peerassessment-{$blockid}-dropdown" data-toggle="collapse" aria-expanded="false" aria-controls="peerassessment-{$blockid}-dropdown" class="collapsed">
                                        {str tag=instructions section=blocktype.peerassessment/peerassessment}
                                        <span class="icon icon-chevron-down collapse-indicator right text-inline"></span>
                                    </a>
                                </h4>
                            </legend>
                            <div class="fieldset-body collapse" id="peerassessment-{$blockid}-dropdown">
                                {$instructions|safe}
                            </div>
                        </fieldset>
                    </div>
                    {/if}
                    {if $allowfeedback && !$editing}
                    <div id="add_assessment_feedback_{$blockid}">
                        {$addassessmentfeedbackform|safe}
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>

{/if}

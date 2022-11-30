{if $noassessment && $editing}
    <p class="editor-description">{$noassessment}</p>
{else}
    {if $allowfeedback}
        <a id="add_checkpoint_feedback_link" class="js-checkpoint-modal feedback link-blocktype" href="#"
            data-bs-toggle="modal-docked" data-bs-target="#checkpoint_{$blockid}" data-blockid="{$blockid}">
            <span class="icon icon-plus" role="presentation" aria-hidden="true"></span>
            {str tag=addcheckpointfeedback section=blocktype.checkpoint/checkpoint}
        </a>
    {elseif $exporter}
        {if $instructions}
            <div>{str tag='instructions' section='view'}</div>
            <div class="viewinstruction-export">
                {$instructions|safe}
            </div>
        {/if}
    {/if}
{/if}
{if !$editing}
    {* Do not change the id because it is used by paginator.js *}
    <div id="checkpointfeedbacktable{$blockid}" class="feedbacktable js-feedbackblock fullwidth">
    {if $feedback}
        {$feedback->tablerows|safe}
        {$feedback->pagination|safe}
        {if $feedback->pagination_js}
        <script type="application/javascript">
            jQuery(function () {
                checkpointpaginator{$blockid} = {$feedback->pagination_js|safe}
            });
        </script>
        {/if}
    {/if}
    </div>

    <!-- modal for the checkpoint feedback -->
    <div id="checkpoint_{$blockid}" class="feedbacktable modal modal-docked">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="btn-close" data-bs-dismiss="modal-docked">
                        <span class="times">&times;</span>
                        <span class="visually-hidden">{str tag=Close}</span>
                    </button>
                    <h1 class="modal-title">
                        <span class="icon icon-check" role="presentation" aria-hidden="true"></span>
                        {str tag=title section=blocktype.checkpoint/checkpoint}
                    </h1>
                </div>
                <div class="modal-body">
                    {if $allowfeedback && !$editing}
                    <div id="add_checkpoint_feedback_form_{$blockid}">
                        {$addcheckpointfeedbackform|safe}
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>

{/if}

{if $sitetemplate}
    {str tag=checkpointsitetemplate section=blocktype.checkpoint/checkpoint}
{else}
    <form id="checkpoint_levels_{$blockid}"
    class="form-pagination js-pagination form-inline pagination-page-limit dropdown">
        {include file="blocktype:checkpoint:achievement_levels_dropdown.tpl"}
        {include file="blocktype:checkpoint:display_achievement_level.tpl"}
    </form>
    <p class="editor-description">{$noassessment}</p>
    <a id="add_checkpoint_feedback_link" class="js-checkpoint-modal feedback link-blocktype" href="#"
        data-bs-toggle="modal-docked" data-bs-target="#checkpoint_{$blockid}" data-blockid="{$blockid}">
        <span class="icon icon-plus" role="presentation" aria-hidden="true"></span>
        {str tag=addcheckpointfeedback section=blocktype.checkpoint/checkpoint}
    </a>
{/if}
{* Do not change the id because it is used by paginator.js *}
<div id="checkpointfeedbacktable{$blockid}" class="feedbacktable js-feedbackblock fullwidth">
    {if $feedback}
        {$feedback->tablerows|safe}
        {$feedback->pagination|safe}
        {if $feedback->pagination_js}
        <script type="application/javascript">
            jQuery(function() {
                var checkpointpaginator{$blockid} = {$feedback->pagination_js|safe}
                });
        </script>
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
                        {str tag=modalcheckpointcomment section=blocktype.checkpoint/checkpoint}
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
{if !$editing}
    <div class="comments pull-left">
        {if $commentcount > 0}
        <a class="commentlink link-blocktype" id="block_{$blockid}" data-toggle="modal-docked" data-target="#feedbacktable_{$blockid}" href="#">
            <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
            {str tag=Comments section=artefact.comment} ({$commentcount})
        </a>
        {/if}
        {if $allowcomments}
            <a class="addcomment link-blocktype" href="{$artefacturl}">
                <span class="icon icon-arrow-circle-right" role="presentation" aria-hidden="true"></span>
                {str tag=addcomment section=artefact.comment}
            </a>
        {/if}
    </div>

    <div class="feedback modal modal-docked" id="feedbacktable_{$blockid}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header clearfix">
                    <button class="deletebutton close" data-dismiss="modal-docked">
                        <span class="times">&times;</span>
                        <span class="sr-only">{str tag=Close}</span>
                    </button>
                    <h4 class="modal-title pull-left">
                        <span class="icon icon-lg icon-comments left" role="presentation" aria-hidden="true"></span>
                        {str tag=Comments section=artefact.comment} - {$artefacttitle}
                    </h4>
                    {if $allowcomments}
                    <a class="addcomment pull-right" href="{$artefacturl}">
                        {str tag=addcomment section=artefact.comment}
                        <span class="icon icon-arrow-right right" role="presentation" aria-hidden="true"></span>
                    </a>
                    {/if}
                </div>
                <div class="modal-body flush">
                {$comments->tablerows|safe}
                </div>
            </div>
        </div>
    </div>
{/if}

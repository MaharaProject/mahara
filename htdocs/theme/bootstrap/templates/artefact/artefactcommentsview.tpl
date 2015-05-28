<div class="comments panel-body pt0">
    {if $commentcount > 0}
        <a class="commentlink text-thin pull-left ptl" id="block_{$blockid}" href="{$artefacturl}">
            {str tag=Comments section=artefact.comment} ({$commentcount})
        </a>
    {else}
        {if $allowcomments}
            <span id='block_{$blockid}' class="text-light text-thin nocomments text-default pull-left">
                {str tag=Comments section=artefact.comment} ({$commentcount})
            </span>
        {/if}
        {if $allowcomments}
            {if !$editing}
            <p class="text-right">
                <a class="text-thin" href="{$artefacturl}">
                    <span class="fa fa-lg fa-plus text-primary prs"></span>
                    {str tag=addcomment section=artefact.comment}
                </a>
            </p>
            {/if}
        {/if}
    {/if}
</div>
{if !$editing}
<div class="feedbacktablewrapper">
    <div id="feedbacktable_{$blockid}" class="feedbacktable">
        {$comments->tablerows|safe}
    </div>
</div>
{/if}
<div class="block-header d-none">
    {if $allowcomments}
        <a class="commentlink" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$artefactid}">
        {if $commentcount > 0}
            <span class="comment_count" role="presentation" aria-hidden="true"></span>
            <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
            {str tag=commentsanddetails section=artefact.comment arg1=$commentcount}
        {else}
            <span class="icon icon-plus" role="presentation" aria-hidden="true"></span>
            {str tag=addcomment section=artefact.comment}
            <span class="bh-margin-left icon icon-link" role="presentation" aria-hidden="true"></span>
            {str tag=Details section=artefact.comment}
        {/if}
        </a>
    {/if}
    {if $allowdetails}
        <a class="modal_link list-group-heading" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$artefactid}">
            <span class="icon icon-link" role="presentation" aria-hidden="true"></span>
            {str tag=Details section=artefact.comment}
        </a>
    {/if}
</div>

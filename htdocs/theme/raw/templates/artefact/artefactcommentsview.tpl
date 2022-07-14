{if !$editing}
    <div class="comments float-start">
        {if $allowcomments}
            <a class="commentlink link-blocktype" data-bs-toggle="modal-docked" data-bs-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$artefactid}">
                <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
                <span class="comment_count" role="presentation" aria-hidden="true"></span>
                {str tag=commentsanddetails section=artefact.comment arg1=$commentcount}
            </a>
        {/if}
    </div>
{/if}

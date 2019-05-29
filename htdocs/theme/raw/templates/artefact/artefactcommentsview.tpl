{if !$editing}
    <div class="comments float-left">
        {if $allowcomments}
            <a id="block_{$blockid}" class="commentlink link-blocktype" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$artefactid}">
                <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
                <span class="comment_count" role="presentation" aria-hidden="true"></span>
                {str tag=commentsanddetails section=artefact.comment arg1=$commentcount}
            </a>
        {/if}
    </div>
{/if}

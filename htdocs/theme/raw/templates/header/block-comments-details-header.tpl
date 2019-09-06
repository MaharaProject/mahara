<div class="block-header d-none {if $displayiconsonly}btn-top-right btn-group-top bh-displayiconsonly{/if}">
    {if $allowcomments}
        <a class="commentlink {if $displayiconsonly}btn btn-secondary{/if}"
            data-toggle="modal-docked"
            data-target="#configureblock"
            href="#"
            data-blockid="{$blockid}"
            data-artefactid="{$artefactid}"
            title="{if $commentcount > 0}{str tag=Comments section=artefact.comment} {str tag=anddetails section=artefact.comment}{/if}">
        {if $commentcount > 0}
            <span class="comment_count" role="presentation" aria-hidden="true"></span>
            <span class="icon icon-comments" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag=Comments section=artefact.comment} {str tag=anddetails section=artefact.comment}</span>
            {if $displayiconsonly}
                ({$commentcount})
                <span class="bh-margin-left icon icon-search-plus" role="presentation" aria-hidden="true"></span>
            {else}
                {str tag=commentsanddetails section=artefact.comment arg1=$commentcount}
            {/if}
        {else}
            {if $displayiconsonly}
                <span class="icon icon-comments" role="presentation" aria-hidden="true" title="{str tag=addcomment section=artefact.comment}"></span>
                <span class="bh-margin-left icon icon-search-plus" role="presentation" aria-hidden="true" title="{str tag=Details section=artefact.comment}"></span>
            {else}
                <span class="icon icon-plus" role="presentation" aria-hidden="true" title="{str tag=Comments section=artefact.comment} {str tag=anddetails section=artefact.comment}"></span>
                {str tag=addcomment section=artefact.comment}
                <span class="bh-margin-left icon icon-search-plus" role="presentation" aria-hidden="true"></span>
                {str tag=Details section=mahara}
            {/if}
        {/if}
        </a>
    {else if $justdetails}
        <a class="modal_link list-group-heading {if $displayiconsonly}btn btn-secondary{/if}"
            data-toggle="modal-docked"
            data-target="#configureblock"
            href="#"
            data-blockid="{$blockid}"
            data-artefactid="{$artefactid}"
            title="{str tag=Details section=artefact.comment}">
            <span class="icon icon-search-plus" role="presentation" aria-hidden="true" title="{str tag=Details section=artefact.comment}"></span>
            {if !$displayiconsonly}
                {str tag=Details section=mahara}
            {/if}
        </a>
    {/if}
</div>

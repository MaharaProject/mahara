<div class="card-body">
{if !$editing}
    {if !$allowcomments}
        {assign var="justdetails" value=true}
    {/if}
    {include
        file='header/block-comments-details-header.tpl'
        artefactid=$artefactid
        blockid=$blockid
        commentcount=$commentcount
        allowcomments=$allowcomments
        justdetails=$justdetails
        displayiconsonly=true}
{/if}
{$html|safe}
</div>

    {if !$editing && $blockheader && !$versioning && !$peerroleonly}
        {include file='header/block-comments-details-header.tpl' artefactid=$artefactid blockid=$blockid commentcount=$commentcount showquickedit=$showquickedit displayiconsonly=$displayiconsonly}
    {elseif $showquickedit}
        <div class="block-header quick-edit d-none">
        {include file='header/block-quickedit-header.tpl' blockid=$blockid}
        </div>
    {/if}
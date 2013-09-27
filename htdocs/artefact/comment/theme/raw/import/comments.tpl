{if count($entrycomments)}
<div class="section fullwidth">
    <h2>{str tag=Comment section=artefact.comment}</h2>
</div>
{foreach from=$entrycomments item=comment}
<div class="{cycle name=rows values='r0,r1'} listrow">
    <div id="entrycomment" class="indent1 fullwidth">
        <div class="importcolumn importcolumn1">
            <div id="{$comment.id}_desc" class="detail">{$comment.description|clean_html|safe}</div>
        </div>
        <div class="importcolumn importcolumn2">
        </div>
        <div class="importcolumn importcolumn3">
            {foreach from=$displaydecisions key=opt item=displayopt}
                {if !$comment.disabled[$opt]}
                <input class="commentdecision" id="{$comment.id}" type="radio" name="decision_{$comment.id}" value="{$opt}"{if $comment.decision == $opt} checked="checked"{/if}>
                {$displayopt}<br>
                {/if}
            {/foreach}
        </div>
        <div class="cb"></div>
    </div>
</div>
{/foreach}
<script type="text/javascript">
    jQuery(function() {
        jQuery("a.commenttitle").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_desc").toggleClass("hidden");
        });
    });
</script>
{/if}

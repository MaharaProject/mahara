<div class="section fullwidth">
    <h2>{str tag=View section=view}</h2>
</div>
{foreach from=$entryviews item=view}
<div class="{cycle name=rows values='r0,r1'} listrow">
    <div id="entryview" class="indent1 fullwidth">
        <div class="importcolumn importcolumn1">
            <h3 class="title">
            {if $view.description}<a class="viewtitle" href="" id="{$view.id}">{/if}
            {$view.title|str_shorten_text:80:true}
            {if $view.description}</a>{/if}
            </h3>
            <div id="{$view.id}_desc" class="detail hidden">{$view.description|clean_html|safe}</div>
        </div>
        <div class="importcolumn importcolumn2">
        </div>
        <div class="importcolumn importcolumn3">
            {foreach from=$displaydecisions key=opt item=displayopt}
                {if !$view.disabled[$opt]}
                <input id="decision_{$view.id}_{$opt}" class="viewdecision" id="{$view.id}" type="radio" name="decision_{$view.id}" value="{$opt}"{if $view.decision == $opt} checked="checked"{/if}>
                <label for="decision_{$view.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$view.title})</span></label><br>
                {/if}
            {/foreach}
        </div>
        <div class="cb"></div>
    </div>
</div>
{/foreach}
<script type="text/javascript">
    jQuery(function() {
        jQuery("a.viewtitle").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_desc").toggleClass("hidden");
        });
    });
</script>

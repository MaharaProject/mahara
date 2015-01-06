{if count($entryannotations)}
<div class="section fullwidth">
    <h2>{str tag=Annotation section=artefact.annotation}</h2>
</div>
{foreach from=$entryannotations item=annotation}
<div class="{cycle name=rows values='r0,r1'} listrow">
    <div id="entryannotation" class="indent1">
        <div class="importcolumn importcolumn1">
            <h3 class="title">
            {if $annotation.description}<a class="annotationtitle hidden" href="" id="{$annotation.id}">{/if}
            {$annotation.title|str_shorten_text:80:true}
            {if $annotation.description}</a>{/if}
            </h3>
            <div id="{$annotation.id}_desc" class="detail">{$annotation.description|clean_html|safe}</div>
            {if $annotation.tags}
            <div class="tags">
                <strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$annotation.tags}
            </div>
            {/if}
            <div class="annotationfeedback">
                <strong>{str tag=Annotationfeedback section=artefact.annotation}:</strong> <a class="showannotationfeedback" href="" id="{$annotation.id}">{str tag=nannotationfeedback section=artefact.annotation arg1=count($annotation.annotationfeedback)}</a>
            </div>
        </div>
        <div class="importcolumn importcolumn2">
            {if $annotation.duplicateditem}
            <div class="duplicatedannotation">
                <strong>{str tag=duplicatedannotation section=artefact.annotation}:</strong> <a class="showduplicatedannotation" href="" id="{$annotation.duplicateditem.id}">{$annotation.duplicateditem.title|str_shorten_text:80:true}</a>
                <div id="{$annotation.duplicateditem.id}_duplicatedannotation" class="detail hidden">{$annotation.duplicateditem.html|clean_html|safe}</div>
            </div>
            {/if}
            {if $annotation.existingitems}
            <div class="existingannotations">
                <strong>{str tag=existingannotation section=artefact.annotation}:</strong>
                   {foreach from=$annotation.existingitems item=existingitem}
                   <a class="showexistingannotation" href="" id="{$existingitem.id}">{$existingitem.title|str_shorten_text:80:true}</a><br>
                   <div id="{$existingitem.id}_existingannotation" class="detail hidden">{$existingitem.html|clean_html|safe}</div>
                   {/foreach}
            </div>
            {/if}
        </div>
        <div class="importcolumn importcolumn3">
            {foreach from=$displaydecisions key=opt item=displayopt}
                {if !$annotation.disabled[$opt]}
                <input id="decision_{$annotation.id}_{$opt}" class="annotationdecision" id="{$annotation.id}" type="radio" name="decision_{$annotation.id}" value="{$opt}"{if $annotation.decision == $opt} checked="checked"{/if}>
                <label for="decision_{$annotation.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$annotation.title})</span></label><br>
                {/if}
            {/foreach}
        </div>
        <div class="cb"></div>
    </div>
    <div id="{$annotation.id}_annotationfeedback" class="indent2 hidden">
    {foreach from=$annotation.annotationfeedback item=feedback}
        <div id="annotationfeedbacktitle_{$feedback.id}" class="{cycle name=rows values='r0,r1'} listrow {if $feedback.private}private{else}public{/if}">
            <div class="importcolumn importcolumn1">
                <h4 class="title">
                    {if $feedback.description}<a class="annotationfeedbacktitle" href="" id="{$feedback.id}">{/if}
                    {$feedback.title|str_shorten_text:80:true}
                    {if $feedback.description}</a>{/if}
                </h4>
                <div id="{$feedback.id}_desc" class="detail hidden">
                    {$feedback.description|clean_html|safe}
                </div>
                <span id="annotationfeedbackstatus{$feedback.id}" class="annotationfeedbackstatus">
                    {if $feedback.private}
                        {str tag=private section=artefact.annotation}
                    {else}
                        {str tag=public section=artefact.annotation}
                    {/if}
                </span>
                <div id="annotationfeedbackdetails_{$feedback.id}" class="annotationfeedbackdetails">
                    {str tag=enteredon section=artefact.annotation} {$feedback.ctime}
                </div>
            </div>
            <div class="importcolumn importcolumn2">
                {if $feedback.duplicateditem}
                <div class="duplicatedannotationfeedback">
                    <strong>{str tag=duplicatedannotationfeedback section=artefact.annotation}:</strong> <a class="showduplicatedannotationfeedback" href="" id="{$feedback.duplicateditem.id}">{$feedback.duplicateditem.title|str_shorten_text:80:true}</a>
                    <div id="{$feedback.duplicateditem.id}_duplicatedannotationfeedback" class="detail hidden">{$feedback.duplicateditem.html|clean_html|safe}</div>
                </div>
                {/if}
                {if $feedback.existingitems}
                <div class="existingannotationfeedback">
                    <strong>{str tag=existingannotationfeedback section=artefact.annotation}:</strong>
                       {foreach from=$feedback.existingitems item=existingitem}
                       <a class="showexistingannotationfeedback" href="" id="{$existingitem.id}">{$existingitem.title|str_shorten_text:80:true}</a><br>
                       <div id="{$existingitem.id}_existingannotationfeedback" class="detail hidden">{$existingitem.html|clean_html|safe}</div>
                       {/foreach}
                </div>
                {/if}
            </div>
            <div class="importcolumn importcolumn3">
                {foreach from=$displaydecisions key=opt item=displayopt}
                    {if !$feedback.disabled[$opt]}
                    <input id="decision_{$feedback.id}_{$opt}" class="annotationfeedbackdecision" type="radio" name="decision_{$feedback.id}" value="{$opt}"{if $feedback.decision == $opt} checked="checked"{/if}>
                    <label for="decision_{$feedback.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$feedback.title})</span></label><br>
                    {/if}
                {/foreach}
            </div>
            <div class="cb"></div>
        </div>
    {/foreach}
    </div>
    <div class="cb"></div>
</div>
{/foreach}
<script type="application/javascript">
    jQuery(function() {
        jQuery("a.showduplicatedannotation").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_duplicatedannotation").toggleClass("hidden");
        });
        jQuery("a.showexistingannotation").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_existingbannotation").toggleClass("hidden");
        });
        jQuery("a.showduplicatedannotationfeedback").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_duplicatedannotationfeedback").toggleClass("hidden");
        });
        jQuery("a.showexistingannotationfeedback").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_existingannotationfeedback").toggleClass("hidden");
        });
        jQuery("a.showannotationfeedback").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_annotationfeedback").toggleClass("hidden");
        });
        jQuery("input.annotationdecision").change(function(e) {
            e.preventDefault();
            if (this.value == '1') {
                // The import decision for the annotation is IGNORE
                // Set decision for its annotationfeedback to be IGNORE as well
                jQuery("#" + this.id + "_annotationfeedback input.annotationfeedbackdecision[value=1]").prop('checked', true);
            }
        });
    });
</script>
{/if}

{if is_array($entryannotations) && count($entryannotations)}
<div class="section-import">
    <h2>{str tag=Annotation section=artefact.annotation}</h2>
    {foreach from=$entryannotations item=annotation}
    <div class="list-group-item">
        <div id="entryannotation" class="row">
            <div class="col-md-8">
                <h3 class="title list-group-item-heading">
                    {$annotation.title|str_shorten_text:80:true}
                </h3>
                <div id="{$annotation.id}_desc" class="detail">
                    {$annotation.description|clean_html|safe}
                </div>
                {if $annotation.tags}
                <div class="tags">
                    <strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$annotation.tags}
                </div>
                {/if}
                <div class="annotationfeedback">
                    <strong>{str tag=Annotationfeedback section=artefact.annotation}:</strong>
                    {str tag=nannotationfeedback section=artefact.annotation arg1=count($annotation.annotationfeedback)}
                </div>
                <!-- TODO Display existing annotation and annotation count with section title -->
                <!-- {if $annotation.existingitems}
                <div class="exsitingannotation">
                    <strong>{str tag=existingannotation section=artefact.annotation}:</strong>
                    <span>({count($annotation.existingitems)})</span>
                </div>
                {/if} -->
                {if $annotation.duplicateditem}
                <div class="duplicatedannotation">
                    <strong class="text-warning">{str tag=duplicatedannotation section=artefact.annotation}</strong>
                </div>
                {/if}
            </div>
            <div class="col-md-4">
                {foreach from=$displaydecisions key=opt item=displayopt}
                    {if !$annotation.disabled[$opt]}
                    <label for="decision_{$annotation.id}_{$opt}">
                        <input id="decision_{$annotation.id}_{$opt}" class="annotationdecision" id="{$annotation.id}" type="radio" name="decision_{$annotation.id}" value="{$opt}"{if $annotation.decision == $opt} checked="checked"{/if}>
                        {$displayopt}
                        <span class="accessible-hidden">({$annotation.title})</span>
                    </label>
                    {/if}
                {/foreach}
            </div>
        </div>
        <div id="{$annotation.id}_annotationfeedback" class="annotationfeedback list-group list-group-lite">
            {foreach from=$annotation.annotationfeedback item=feedback}
            <div class="list-group-item">
                <div id="annotationfeedbacktitle_{$feedback.id}" class="{if $feedback.private}private{else}public{/if} row">
                    <div class="col-md-8">
                        <h4 class="title list-group-item-heading">
                            {$feedback.title|str_shorten_text:80:true}
                        </h4>
                        <div id="{$feedback.id}_desc" class="detail d-none">
                            {$feedback.description|clean_html|safe}
                        </div>
                        <span id="annotationfeedbackstatus{$feedback.id}" class="annotationfeedbackstatus text-small text-midtone">
                            {if $feedback.private}
                                {str tag=private section=artefact.annotation}
                            {else}
                                {str tag=public section=artefact.annotation}
                            {/if}
                        </span>
                        <div id="annotationfeedbackdetails_{$feedback.id}" class="annotationfeedbackdetails text-small text-midtone">
                            {str tag=enteredon section=artefact.annotation} {$feedback.ctime}
                        </div>
                        <!-- TODO Display existing annotation feedbacks and annotation feedback count with section title -->
                        <!-- {if $feedback.existingitems}
                        <div class="existingannotationfeedback">
                            <strong>{str tag=existingannotationfeedback section=artefact.annotation}:</strong>
                            <span>({count($feedback.existingitems)})</span>
                        </div>
                        {/if} -->
                        {if $feedback.duplicateditem}
                        <div class="duplicatedannotationfeedback">
                            <strong class="text-warning">{str tag=duplicatedannotationfeedback section=artefact.annotation}</strong>
                        </div>
                        {/if}
                    </div>
                    <div class="col-md-4">
                        {foreach from=$displaydecisions key=opt item=displayopt}
                            {if !$feedback.disabled[$opt]}
                            <label for="decision_{$feedback.id}_{$opt}">
                                <input id="decision_{$feedback.id}_{$opt}" class="annotationfeedbackdecision" type="radio" name="decision_{$feedback.id}" value="{$opt}"{if $feedback.decision == $opt} checked="checked"{/if}>
                                {$displayopt}
                                <span class="accessible-hidden">({$feedback.title})</span>
                            </label>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
    </div>
    {/foreach}
</div>
<script>
    jQuery(function() {
        jQuery("input.annotationdecision").on("change", function(e) {
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

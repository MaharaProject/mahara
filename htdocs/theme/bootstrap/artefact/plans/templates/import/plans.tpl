{if count($entryplans)}
<div class="section fullwidth">
    <h2>{str tag=plan section=artefact.plans}</h2>
</div>
{foreach from=$entryplans item=plan}
<div class="{cycle name=rows values='r0,r1'} listrow">
    <div id="entryplan" class="indent1">
        <div class="importcolumn importcolumn1">
            <h3 class="title">
            {if $plan.description}<a class="plantitle" href="" id="{$plan.id}">{/if}
            {$plan.title|str_shorten_text:80:true}
            {if $plan.description}</a>{/if}
            </h3>
            <div id="{$plan.id}_desc" class="detail hidden">{$plan.description|clean_html|safe}</div>
            {if $plan.tags}
            <div class="tags">
                <strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$plan.tags}
            </div>
            {/if}
            <div class="tasks">
                <strong>{str tag=tasks section=artefact.plans}:</strong>
                {if count($plan.entrytasks)}<a class="showtasks" href="" id="{$plan.id}">{/if}
                {str tag=ntasks section=artefact.plans arg1=count($plan.entrytasks)}
                {if count($plan.entrytasks)}</a>{/if}
            </div>
        </div>
        <div class="importcolumn importcolumn2">
            {if $plan.duplicateditem}
            <div class="duplicatedplan">
                <strong>{str tag=duplicatedplan section=artefact.plans}:</strong> <a class="showduplicatedplan" href="" id="{$plan.duplicateditem.id}">{$plan.duplicateditem.title|str_shorten_text:80:true}</a>
                <div id="{$plan.duplicateditem.id}_duplicatedplan" class="detail hidden">{$plan.duplicateditem.html|clean_html|safe}</div>
            </div>
            {/if}
            {if $plan.existingitems}
            <div class="existingplans">
                <strong>{str tag=existingplans section=artefact.plans}:</strong>
                   {foreach from=$plan.existingitems item=existingitem}
                   <a class="showexistingplan" href="" id="{$existingitem.id}">{$existingitem.title|str_shorten_text:80:true}</a><br>
                   <div id="{$existingitem.id}_existingplan" class="detail hidden">{$existingitem.html|clean_html|safe}</div>
                   {/foreach}
            </div>
            {/if}
        </div>
        <div class="importcolumn importcolumn3">
            {foreach from=$displaydecisions key=opt item=displayopt}
                {if !$plan.disabled[$opt]}
                <input id="decision_{$plan.id}_{$opt}" class="plandecision" id="{$plan.id}" type="radio" name="decision_{$plan.id}" value="{$opt}"{if $plan.decision == $opt} checked="checked"{/if}>
                <label for="decision_{$plan.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$plan.title})</span></label><br>
                {/if}
            {/foreach}
        </div>
        <div class="cb"></div>
    </div>
    <div id="{$plan.id}_tasks" class="indent2 hidden">
    {foreach from=$plan.entrytasks item=task}
        <div id="tasktitle_{$task.id}" class="{cycle name=rows values='r0,r1'} listrow">
            <div class="importcolumn importcolumn1">
                <h4 class="title"><a class="tasktitle" href="" id="{$task.id}">{$task.title|str_shorten_text:80:true}</a></h4>
                <div id="{$task.id}_desc" class="detail hidden">
                    {$task.description|clean_html|safe}
                </div>
                <div class="completiondate"><strong>{str tag='completiondate' section='artefact.plans'}:</strong> {$task.completiondate}</div>
                {if $task.completed == 1}<div class="completed">{str tag=completed section=artefact.plans}</div>{/if}
            </div>
            <div class="importcolumn importcolumn2">
            &nbsp;
            </div>
            <div class="importcolumn importcolumn3">
                {foreach from=$displaydecisions key=opt item=displayopt}
                    {if !$task.disabled[$opt]}
                    <input id="decision_{$task.id}_{$opt}" class="taskdecision" type="radio" name="decision_{$task.id}" value="{$opt}"{if $task.decision == $opt} checked="checked"{/if}>
                    <label for="decision_{$task.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$task.title})</span></label><br>
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
<script type="text/javascript">
    jQuery(function() {
        jQuery("a.plantitle").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_desc").toggleClass("hidden");
        });
        jQuery("a.tasktitle").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_desc").toggleClass("hidden");
        });
        jQuery("a.showduplicatedplan").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_duplicatedplan").toggleClass("hidden");
        });
        jQuery("a.showexistingplan").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_existingplan").toggleClass("hidden");
        });
       jQuery("a.showtasks").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_tasks").toggleClass("hidden");
        });
        jQuery("input.plandecision").change(function(e) {
            e.preventDefault();
            if (this.value == '1') {
            // The import decision for the plan is IGNORE
            // Set decision for its tasks to be IGNORE as well
                jQuery("#" + this.id + "_tasks input.taskdecision[value=1]").prop('checked', true);
            }
        });
    });
</script>
{/if}

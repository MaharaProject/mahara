<div class="section-import">
    <h2>{str tag=plan section=artefact.plans}</h2>
    {foreach from=$entryplans item=plan}
    <div class="list-group-item">
        <div id="entryplan-{$plan.id}" class="row">
            <div class="col-md-8">
                <h3 class="title list-group-item-heading">
                    {$plan.title|str_shorten_text:80:true}
                </h3>
                {if $plan.description}
                <div id="{$plan.id}_desc" class="detail hidden">
                    {$plan.description|clean_html|safe}
                </div>
                {/if}{if $plan.tags}
                <div class="tags">
                    <strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$plan.tags}
                </div>
                {/if}
                <div class="tasks">
                    <strong>{str tag=tasks section=artefact.plans}:</strong>
                    {str tag=ntasks section=artefact.plans arg1=count($plan.entrytasks)}
                </div>
                <!-- TODO Display existing plans and plan count with section title -->
                <!-- {if $plan.existingitems}
                <div class="existingplans">
                    <strong>{str tag=existingplans section=artefact.plans}</strong>
                    <span>({count($plan.existingitems)})</span>
                </div>
                {/if} -->
                {if $plan.duplicateditem}
                <div class="duplicatedplan">
                    <strong class="text-warning">{str tag=duplicatedplan section=artefact.plans}</strong>
                </div>
                {/if}
            </div>
            <div class="col-md-4">
                {foreach from=$displaydecisions key=opt item=displayopt}
                    {if !$plan.disabled[$opt]}
                    <label for="decision_{$plan.id}_{$opt}">
                        <input id="decision_{$plan.id}_{$opt}" class="plandecision" id="{$plan.id}" type="radio" name="decision_{$plan.id}" value="{$opt}"{if $plan.decision == $opt} checked="checked"{/if}>
                        {$displayopt}
                        <span class="accessible-hidden sr-only">
                            ({$plan.title})
                        </span>
                    </label>
                    {/if}
                {/foreach}
            </div>
        </div>
        {if $plan.entrytasks}
        <div id="{$plan.id}_tasks" class="list-group list-group-lite">
            {foreach from=$plan.entrytasks item=task}
            <div class="list-group-item">
                <div id="tasktitle_{$task.id}" class="row">
                    <div class="col-md-8">
                        <h4 class="title list-group-item-heading text-inline">
                            <a class="tasktitle" href="" id="{$task.id}">
                                {$task.title|str_shorten_text:80:true}
                            </a>
                        </h4>
                        {if $task.completed == 1}
                        <span class="completed text-small text-midtone">
                            ({str tag=completed section=artefact.plans})
                        </span>
                        {/if}
                        <div id="{$task.id}_desc" class="detail hidden">
                            {$task.description|clean_html|safe}
                        </div>
                        <div class="completiondate text-small">
                            <strong>{str tag='completiondate' section='artefact.plans'}:</strong> {$task.completiondate}
                        </div>
                    </div>
                    <div class="col-md-4">
                        {foreach from=$displaydecisions key=opt item=displayopt}
                            {if !$task.disabled[$opt]}
                            <label for="decision_{$task.id}_{$opt}">
                                <input id="decision_{$task.id}_{$opt}" class="taskdecision" type="radio" name="decision_{$task.id}" value="{$opt}"{if $task.decision == $opt} checked="checked"{/if}>
                                {$displayopt}
                                <span class="accessible-hidden sr-only">({$task.title})</span>
                            </label>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            </div>
            {/foreach}
        </div>
        {/if}
    </div>
    {/foreach}
</div>
<script type="application/javascript">
    jQuery(function() {
        jQuery("a.tasktitle").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_desc").toggleClass("hidden");
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

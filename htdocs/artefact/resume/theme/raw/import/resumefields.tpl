<div class="section fullwidth">
    <h2>{str tag=resume section=artefact.resume}</h2>
</div>
{foreach from=$resumegroups item=resumegroup}
{if count($resumegroup.fields)}
<fieldset id="{$resumegroup.id}_fs" class="indent1 collapsible collapsed">
    <legend><a id="{$resumegroup.id}" class="resumegroup" href="">{$resumegroup.legend}</a></legend>
    {foreach from=$resumegroup.fields key=fieldname item=fieldvalues}
        {if count(fieldvalues)}
        <div id="resumefield" class="indent2">
            <h4 class="resumefield">{$fieldname}</h3>
            {foreach from=$fieldvalues item=fieldvalue}
                <div id="resumefield_{$fieldvalue.id}" class="{cycle name=rows values='r0,r1'} listrow">
                    <div class="importcolumn importcolumn1">
                        <div id="{$fieldvalue.id}_desc" class="detail">
                            {$fieldvalue.html|clean_html|safe}
                        </div>
                    </div>
                    <div class="importcolumn importcolumn2">
                        {if $fieldvalue.duplicateditem}
                        <div class="duplicatedpfield">
                            <strong>{str tag=duplicatedresumefieldvalue section=artefact.resume}:</strong>
                            <div id="{$fieldvalue.duplicateditem.id}_duplicatedpfield" class="detail">{$fieldvalue.duplicateditem.html|clean_html|safe}</div>
                        </div>
                        {/if}
                        {if $fieldvalue.existingitems}
                        <div class="existingpfields">
                            <strong>{str tag=existingresumefieldvalues section=artefact.resume}:</strong>
                               {foreach from=$fieldvalue.existingitems item=existingitem}
                               <div id="{$existingitem.id}_existingresumefield" class="detail">{$existingitem.html|clean_html|safe}</div>
                               {/foreach}
                        </div>
                        {/if}
                    </div>
                    <div class="importcolumn importcolumn3">
                        {foreach from=$displaydecisions key=opt item=displayopt}
                            {if !$fieldvalue.disabled[$opt]}
                            <input id="decision_{$fieldvalue.id}_{$opt}" class="fieldvaluedecision" type="radio" name="decision_{$fieldvalue.id}" value="{$opt}"{if $fieldvalue.decision == $opt} checked="checked"{/if}>
                            <label for="decision_{$fieldvalue.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$fieldname})</span></label><br>
                            {/if}
                        {/foreach}
                    </div>
                    <div class="cb"></div>
                </div>
            {/foreach}
        </div>
        {/if}
    {/foreach}
</fieldset>
{/if}
{/foreach}
<script type="text/javascript">
    jQuery(function() {
        jQuery("a.resumegroup").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_fs").toggleClass("collapsed");
        });
    });
</script>

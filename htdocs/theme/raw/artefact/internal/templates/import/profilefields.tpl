<div class="section fullwidth">
    <h2>{str tag=profile section=artefact.internal}</h2>
</div>
{foreach from=$profilegroups item=profilegroup}
{if count($profilegroup.fields)}
<fieldset id="{$profilegroup.id}_fs" class="indent1 collapsible collapsed">
    <legend><a id="{$profilegroup.id}" class="profilegroup" href="">{$profilegroup.legend}</a></legend>
    {foreach from=$profilegroup.fields key=fieldname item=fieldvalues}
        {if count($fieldvalues)}
        <div id="profilefield" class="indent2">
            <h4 class="profilefield">{str tag=$fieldname section=artefact.internal}</h3>
            {foreach from=$fieldvalues item=fieldvalue}
                <div id="profilefield_{$fieldvalue.id}" class="{cycle name=rows values='r0,r1'} listrow">
                    <div class="importcolumn importcolumn1">
                        <div id="{$fieldvalue.id}_desc" class="detail">
                            {$fieldvalue.html|clean_html|safe}
                        </div>
                    </div>
                    <div class="importcolumn importcolumn2">
                        {if $fieldvalue.duplicateditem}
                        <div class="duplicatedpfield">
                            <strong>{str tag=duplicatedprofilefieldvalue section=artefact.internal}:</strong>
                            <div id="{$fieldvalue.duplicateditem.id}_duplicatedpfield" class="detail">{$fieldvalue.duplicateditem.html|clean_html|safe}</div>
                        </div>
                        {/if}
                        {if $fieldvalue.existingitems}
                        <div class="existingpfields">
                            <strong>{str tag=existingprofilefieldvalues section=artefact.internal}:</strong>
                               {foreach from=$fieldvalue.existingitems item=existingitem}
                               <div id="{$existingitem.id}_existingprofilefield" class="detail">{$existingitem.html|clean_html|safe}</div>
                               {/foreach}
                        </div>
                        {/if}
                    </div>
                    <div class="importcolumn importcolumn3">
                        {foreach from=$displaydecisions key=opt item=displayopt}
                            {if !$fieldvalue.disabled[$opt]}
                            <input id="decision_{$fieldvalue.id}_{$opt}" class="fieldvaluedecision" type="radio" name="decision_{$fieldvalue.id}" value="{$opt}"{if $fieldvalue.decision == $opt} checked="checked"{/if}>
                            <label for="decision_{$fieldvalue.id}_{$opt}">
                                {$displayopt}
                                <span class="accessible-hidden">({str tag=$fieldname section=artefact.internal}: {$fieldvalue.html|safe|strip_tags|str_shorten_text:80:true})</span></label><br>
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
        jQuery("a.profilegroup").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_fs").toggleClass("collapsed");
        });
    });
</script>

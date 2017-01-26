<div class="section-import">
    <h2>{str tag=resume section=artefact.resume}</h2>
    <div class="form-group collapsible-group">
        {foreach from=$resumegroups item=resumegroup}
        {if count($resumegroup.fields)}
        <fieldset id="{$resumegroup.id}_fs" class="pieform-fieldset collapsible">
            <legend>
                <h4>
                    <a id="{$resumegroup.id}" class="resumegroup collapsed" href="#resumefield-{$resumegroup.id}" data-toggle="collapse" aria-expanded="false" aria-controls="resumefield">
                        {$resumegroup.legend}
                        <span class="icon icon-chevron-down collapse-indicator right pull-right" role="presentation" aria-hidden="true"></span>
                    </a>
                </h4>
            </legend>
            <div id="resumefield-{$resumegroup.id}" class="collapse list-group">
                {foreach from=$resumegroup.fields key=fieldname item=fieldvalues}
                {if count(fieldvalues)}
                <div id="resume-{$resumegroup.id}" class="list-group-item">
                    <h5 class="resumefield" class="list-group-item-heading">
                        {$fieldname}
                    </h5>
                    <div class="list-group list-group-lite">
                    {foreach from=$fieldvalues item=fieldvalue}
                        <div class="list-group-item">
                            <div id="resumefield_{$fieldvalue.id}" class="row">
                                <div class="col-md-8">
                                    <div id="{$fieldvalue.id}_desc" class="detail">
                                        {$fieldvalue.html|clean_html|safe}
                                    </div>
                                    <!-- TODO Display existing items properly -->
                                    <!-- {if $fieldvalue.existingitems}
                                    <div class="existingpfields">
                                        <strong>{str tag=existingresumefieldvalues section=artefact.resume}</strong>
                                        <span>({count($fieldvalue.existingitems)})</span>
                                    </div>
                                    {/if} -->
                                    {if $fieldvalue.duplicateditem}
                                    <div class="duplicatedpfield">
                                        <strong class="text-warning">{str tag=duplicatedresumefieldvalue section=artefact.resume}</strong>
                                    </div>
                                    {/if}
                                </div>
                                <div class="col-md-4">
                                    {foreach from=$displaydecisions key=opt item=displayopt}
                                        {if !$fieldvalue.disabled[$opt]}
                                        <label for="decision_{$fieldvalue.id}_{$opt}">
                                            <input id="decision_{$fieldvalue.id}_{$opt}" class="fieldvaluedecision" type="radio" name="decision_{$fieldvalue.id}" value="{$opt}"{if $fieldvalue.decision == $opt} checked="checked"{/if}>
                                            {$displayopt}
                                            <span class="accessible-hidden sr-only">({$fieldname})</span>
                                        </label>
                                        {/if}
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                    </div>
                </div>
                {/if}
                {/foreach}
            </div>
        </fieldset>
        {/if}
        {/foreach}
    </div>
</div>

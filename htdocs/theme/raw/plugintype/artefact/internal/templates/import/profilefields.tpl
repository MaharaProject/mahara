<div class="section-import">
    <h2>{str tag=profile section=artefact.internal}</h2>
    <div class="form-group collapsible-group">
        {foreach from=$profilegroups item=profilegroup name='profilegroup'}
        {if count($profilegroup.fields)}
        <fieldset id="{$profilegroup.id}_fs" class="pieform-fieldset collapsible {if $dwoo.foreach.profilegroups.last} last{/if}">
            <legend>
                <h4>
                    <a id="{$profilegroup.id}" class="profilegroup collapsed" href="#profilefield-{$profilegroup.id}" data-toggle="collapse" aria-expanded="false" aria-controls="#profilefield-{$profilegroup.id}">
                        {$profilegroup.legend}
                        <span class="icon icon-chevron-down collapse-indicator right pull-right" role="presentation" aria-hidden="true"></span>
                    </a>
                </h4>
            </legend>
            <div id="profilefield-{$profilegroup.id}" class="collapse list-group">
                {foreach from=$profilegroup.fields key=fieldname item=fieldvalues}
                {if count($fieldvalues)}
                <div id="profile-{$profilegroup.id}" class="fieldset-body">
                    <h5 class="profilefield list-group-item-heading">
                        {str tag=$fieldname section=artefact.internal}
                    </h5>
                    {foreach from=$fieldvalues item=fieldvalue}
                        <div id="profilefield_{$fieldvalue.id}" class="row">
                            <div class="col-md-8">
                                <div id="{$fieldvalue.id}_desc" class="detail">
                                    {$fieldvalue.html|clean_html|safe}
                                </div>
                                {if $fieldvalue.duplicateditem}
                                <div class="duplicatedpfield">
                                    <strong>{str tag=duplicatedprofilefieldvalue section=artefact.internal}:</strong>
                                    <span id="{$fieldvalue.duplicateditem.id}_duplicatedpfield" class="detail">{$fieldvalue.duplicateditem.html|clean_html|safe}</span>
                                </div>
                                {/if}
                                {if $fieldvalue.existingitems}
                                <div class="existingpfields">
                                    <strong>{str tag=existingprofilefieldvalues section=artefact.internal}:</strong>
                                       {foreach from=$fieldvalue.existingitems item=existingitem}
                                       <span id="{$existingitem.id}_existingprofilefield" class="detail">{$existingitem.html|clean_html|safe}</span>
                                       {/foreach}
                                </div>
                                {/if}
                            </div>
                            <div class="col-md-4">
                                {foreach from=$displaydecisions key=opt item=displayopt}
                                    {if !$fieldvalue.disabled[$opt]}
                                    <label for="decision_{$fieldvalue.id}_{$opt}">
                                        <input id="decision_{$fieldvalue.id}_{$opt}" class="fieldvaluedecision" type="radio" name="decision_{$fieldvalue.id}" value="{$opt}"{if $fieldvalue.decision == $opt} checked="checked"{/if}>
                                        {$displayopt}
                                        <span class="accessible-hidden sr-only">({str tag=$fieldname section=artefact.internal}: {$fieldvalue.html|safe|strip_tags|str_shorten_text:80:true})</span>
                                    </label>
                                    {/if}
                                {/foreach}
                            </div>
                        </div>
                    {/foreach}
                </div>
                {/if}
                {/foreach}
            </div>
        </fieldset>
        {/if}
        {/foreach}
    </div>
</div>

{if is_array($entrypeerassessments) && count($entrypeerassessments)}
<div class="section-import">
    <h2>{str tag=peerassessment section=artefact.peerassessment}</h2>
    {foreach from=$entrypeerassessments item=peerassessment}
    <div class="list-group-item">
        <div id="entrypeerassessment" class="row">
            <div class="col-md-8">
                <h3 class="title list-group-item-heading">
                    {$peerassessment.title|str_shorten_text:80:true}
                </h3>
                <div id="{$peerassessment.id}_desc" class="detail">
                    {$peerassessment.description|clean_html|safe}
                </div>
                {if $peerassessment.tags}
                <div class="tags">
                    <strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$peerassessment.tags}
                </div>
                {/if}
                {if $peerassessment.duplicateditem}
                <div class="duplicatedpeerassessment">
                    <strong class="text-warning">{str tag=duplicatedpeerassessment section=artefact.peerassessment}</strong>
                </div>
                {/if}
            </div>
            <div class="col-md-4">
                {foreach from=$displaydecisions key=opt item=displayopt}
                    {if !$peerassessment.disabled[$opt]}
                    <label for="decision_{$peerassessment.id}_{$opt}">
                        <input id="decision_{$peerassessment.id}_{$opt}" class="peerassessmentdecision" id="{$peerassessment.id}" type="radio" name="decision_{$peerassessment.id}" value="{$opt}"{if $peerassessment.decision == $opt} checked="checked"{/if}>
                        {$displayopt}
                        <span class="accessible-hidden">({$peerassessment.title})</span>
                    </label>
                    {/if}
                {/foreach}
            </div>
        </div>
    </div>
    {/foreach}
</div>
{/if}

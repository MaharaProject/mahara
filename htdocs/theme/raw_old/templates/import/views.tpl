{if $entryviews}
<div class="section-import">
    <h2>{str tag=View section=view}</h2>
    {foreach from=$entryviews item=view}
    <div class="list-group-item">
        <div id="entryview-{$view.id}" class="row">
            <div class="col-md-8">
                <h5 class="title list-group-item-heading">
                    {$view.title|str_shorten_text:80:true}
                </h5>
                {if $view.description}
                <div id="{$view.id}_desc" class="detail">
                    {$view.description|clean_html|safe}
                </div>
                {/if}
            </div>
            <div class="col-md-4">
                {foreach from=$displaydecisions key=opt item=displayopt}
                    {if !$view.disabled[$opt]}
                    <label for="decision_{$view.id}_{$opt}">
                        <input id="decision_{$view.id}_{$opt}" class="viewdecision" id="{$view.id}" type="radio" name="decision_{$view.id}" value="{$opt}"{if $view.decision == $opt} checked="checked"{/if}>
                        {$displayopt}
                        <span class="accessible-hidden sr-only">({$view.title})</span>
                    </label>
                    {/if}
                {/foreach}
            </div>
        </div>
    </div>
    {/foreach}
</div>
{/if}

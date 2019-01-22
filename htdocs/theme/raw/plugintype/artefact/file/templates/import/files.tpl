{if is_array($entryfiles) && count($entryfiles)}
<div class="section-import">
    <h2>{str tag=file section=artefact.file}</h2>
    {foreach from=$entryfiles item=file}
    <div class="{cycle name=rows values='r0,r1'} list-group-item">
        <div id="entryfile-{$file.id}" class="row">
            <div class="col-md-8">
                <h5 class="title list-group-item-heading text-inline">
                    {$file.title|str_shorten_text:80:true}
                </h5>
                 <span class="filesize text-small text-midtone">
                    ({$file.filesize|display_size})
                </span>
                <div id="{$file.id}_desc" class="detail">
                    {$file.description|clean_html|safe}
                </div>
                {if $file.tags}
                <div class="tags">
                    <strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$file.tags}
                </div>
                {/if}
            </div>
            <div class="col-md-4">
                {foreach from=$displaydecisions key=opt item=displayopt}
                    {if !$file.disabled[$opt]}
                    <label for="decision_{$file.id}_{$opt}">
                        <input id="decision_{$file.id}_{$opt}" class="filedecision" id="{$file.id}" type="radio" name="decision_{$file.id}" value="{$opt}"{if $file.decision == $opt} checked="checked"{/if}>
                        {$displayopt}
                        <span class="accessible-hidden sr-only">({$file.title})</span>
                    </label>
                    {/if}
                {/foreach}
            </div>
        </div>
    </div>
    {/foreach}
</div>
{/if}

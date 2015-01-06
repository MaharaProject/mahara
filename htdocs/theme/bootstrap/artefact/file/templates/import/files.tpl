{if count($entryfiles)}
<div class="section fullwidth">
    <h2>{str tag=file section=artefact.file}</h2>
</div>
{foreach from=$entryfiles item=file}
<div class="{cycle name=rows values='r0,r1'} listrow">
    <div id="entryfile" class="indent1 fullwidth">
        <div class="importcolumn importcolumn1">
            <h3 class="title">
                {if $file.description}<a class="filetitle" href="" id="{$file.id}">{/if}
                {$file.title|str_shorten_text:80:true}
                {if $file.description}</a>{/if}
            </h3>
            <div id="{$file.id}_desc" class="detail hidden">{$file.description|clean_html|safe}</div>
            {if $file.filesize}
            <div class="filesize">
                {$file.filesize|display_size}
            </div>
            {/if}
            {if $file.tags}
            <div class="tags">
                <strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$file.tags}
            </div>
            {/if}
        </div>
        <div class="importcolumn importcolumn2">
        </div>
        <div class="importcolumn importcolumn3">
            {foreach from=$displaydecisions key=opt item=displayopt}
                {if !$file.disabled[$opt]}
                <input id="decision_{$file.id}_{$opt}" class="filedecision" id="{$file.id}" type="radio" name="decision_{$file.id}" value="{$opt}"{if $file.decision == $opt} checked="checked"{/if}>
                <label for="decision_{$file.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$file.title})</span></label><br>
                {/if}
            {/foreach}
        </div>
        <div class="cb"></div>
    </div>
</div>
{/foreach}
<script type="text/javascript">
    jQuery(function() {
        jQuery("a.filetitle").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_desc").toggleClass("hidden");
        });
    });
</script>
{/if}

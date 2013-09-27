{if count($entryblogs)}
<div class="section fullwidth">
    <h2>{str tag=blog section=artefact.blog}</h2>
</div>
{foreach from=$entryblogs item=blog}
<div class="{cycle name=rows values='r0,r1'} listrow">
    <div id="entryblog" class="indent1">
        <div class="importcolumn importcolumn1">
            <h3 class="title"><a class="blogtitle" href="" id="{$blog.id}">{$blog.title|str_shorten_text:80:true}</a></h3>
            <div id="{$blog.id}_desc" class="detail hidden">{$blog.description|clean_html|safe}</div>
            {if $blog.tags}
            <div class="tags">
                <label>{str tag=tags}:</label> {list_tags owner=0 tags=$blog.tags}
            </div>
            {/if}
            <div class="posts">
                <label>{str tag=blogpost section=artefact.blog}:</label> <a class="showposts" href="" id="{$blog.id}">{str tag=nposts section=artefact.blog arg1=count($blog.entryposts)}</a>
            </div>
        </div>
        <div class="importcolumn importcolumn2">
            {if $blog.duplicateditem}
            <div class="duplicatedblog">
                <label>{str tag=duplicatedblog section=artefact.blog}:</label> <a class="showduplicatedblog" href="" id="{$blog.duplicateditem.id}">{$blog.duplicateditem.title|str_shorten_text:80:true}</a>
                <div id="{$blog.duplicateditem.id}_duplicatedblog" class="detail hidden">{$blog.duplicateditem.html|clean_html|safe}</div>
            </div>
            {/if}
            {if $blog.existingitems}
            <div class="existingblogs">
                <label>{str tag=existingblogs section=artefact.blog}:</label>
                   {foreach from=$blog.existingitems item=existingitem}
                   <a class="showexistingblog" href="" id="{$existingitem.id}">{$existingitem.title|str_shorten_text:80:true}</a><br>
                   <div id="{$existingitem.id}_existingblog" class="detail hidden">{$existingitem.html|clean_html|safe}</div>
                   {/foreach}
            </div>
            {/if}
        </div>
        <div class="importcolumn importcolumn3">
            {foreach from=$displaydecisions key=opt item=displayopt}
                {if !$blog.disabled[$opt]}
                <input class="blogdecision" id="{$blog.id}" type="radio" name="decision_{$blog.id}" value="{$opt}"{if $blog.decision == $opt} checked="checked"{/if}>
                {$displayopt}<br>
                {/if}
            {/foreach}
        </div>
        <div class="cb"></div>
    </div>
    <div id="{$blog.id}_posts" class="indent2 hidden">
    {foreach from=$blog.entryposts item=post}
        <div id="posttitle_{$post.id}" class="{cycle name=rows values='r0,r1'} listrow {if $post.published}published{else}draft{/if}">
            <div class="importcolumn importcolumn1">
                <h4 class="title"><a class="posttitle" href="" id="{$post.id}">{$post.title|str_shorten_text:80:true}</a></h4>
                <div id="{$post.id}_desc" class="detail hidden">
                    {$post.description|clean_html|safe}
                </div>
                <span id="poststatus{$post.id}" class="poststatus">
                    {if $post.published}
                        {str tag=published section=artefact.blog}
                    {else}
                        {str tag=draft section=artefact.blog}
                    {/if}
                </span>
                {if $post.files}
                    <div id="postfiles_{$post.id}">
                        <table class="attachments fullwidth">
                            <tbody>
                                <tr><th colspan=3>{str tag=attachedfiles section=artefact.blog}</th></tr>
                                {foreach from=$post.files item=file}
                                    <tr class="{cycle values='r1,r0'}">
                                        <td class="icon-container"><img src="{$file->icon}" alt=""></td>
                                        <td><a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}">{$file->title}</a></td>
                                        <td>{$file->description}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                {/if}
                <div id="postdetails_{$post.id}" class="postdetails">
                    {str tag=postedon section=artefact.blog} {$post.ctime}
                </div>
            </div>
            <div class="importcolumn importcolumn2">
                {if $post.duplicateditem}
                <div class="duplicatedpost">
                    <label>{str tag=duplicatedpost section=artefact.blog}:</label> <a class="showduplicatedpost" href="" id="{$post.duplicateditem.id}">{$post.duplicateditem.title|str_shorten_text:80:true}</a>
                    <div id="{$post.duplicateditem.id}_duplicatedpost" class="detail hidden">{$post.duplicateditem.html|clean_html|safe}</div>
                </div>
                {/if}
                {if $post.existingitems}
                <div class="existingposts">
                    <label>{str tag=existingposts section=artefact.blog}:</label>
                       {foreach from=$post.existingitems item=existingitem}
                       <a class="showexistingpost" href="" id="{$existingitem.id}">{$existingitem.title|str_shorten_text:80:true}</a><br>
                       <div id="{$existingitem.id}_existingpost" class="detail hidden">{$existingitem.html|clean_html|safe}</div>
                       {/foreach}
                </div>
                {/if}
            </div>
            <div class="importcolumn importcolumn3">
                {foreach from=$displaydecisions key=opt item=displayopt}
                    {if !$post.disabled[$opt]}
                    <input class="postdecision" type="radio" name="decision_{$post.id}" value="{$opt}"{if $post.decision == $opt} checked="checked"{/if}>
                    {$displayopt}<br>
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
        jQuery("a.blogtitle").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_desc").toggleClass("hidden");
        });
        jQuery("a.posttitle").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_desc").toggleClass("hidden");
        });
        jQuery("a.showduplicatedblog").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_duplicatedblog").toggleClass("hidden");
        });
        jQuery("a.showexistingblog").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_existingblog").toggleClass("hidden");
        });
        jQuery("a.showduplicatedpost").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_duplicatedpost").toggleClass("hidden");
        });
        jQuery("a.showexistingpost").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_existingpost").toggleClass("hidden");
        });
        jQuery("a.showposts").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_posts").toggleClass("hidden");
        });
        jQuery("input.blogdecision").change(function(e) {
            e.preventDefault();
            if (this.value == '1') {
            // The import decision for the blog is IGNORE
            // Set decision for its blogposts to be IGNORE as well
                jQuery("#" + this.id + "_posts input.postdecision[value=1]").prop('checked', true);
            }
        });
    });
</script>
{/if}

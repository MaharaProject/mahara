{if count($entryblogs)}
<div class="section fullwidth">
    <h2>{str tag=blog section=artefact.blog}</h2>
</div>
{foreach from=$entryblogs item=blog}
<div class="{cycle name=rows values='r0,r1'} listrow">
    <div id="entryblog" class="indent1">
        <div class="importcolumn importcolumn1">
            <h3 class="title">
            {if $blog.description}<a class="blogtitle" href="" id="{$blog.id}">{/if}
            {$blog.title|str_shorten_text:80:true}
            {if $blog.description}</a>{/if}
            </h3>
            <div id="{$blog.id}_desc" class="detail hidden">{$blog.description|clean_html|safe}</div>
            {if $blog.tags}
            <div class="tags">
                <strong>{str tag=tags}:</strong> {list_tags owner=0 tags=$blog.tags}
            </div>
            {/if}
            <div class="posts">
                <strong>{str tag=blogpost section=artefact.blog}:</strong> <a class="showposts" href="" id="{$blog.id}">{str tag=nposts section=artefact.blog arg1=count($blog.entryposts)}</a>
            </div>
        </div>
        <div class="importcolumn importcolumn2">
            {if $blog.duplicateditem}
            <div class="duplicatedblog">
                <strong>{str tag=duplicatedblog section=artefact.blog}:</strong> <a class="showduplicatedblog" href="" id="{$blog.duplicateditem.id}">{$blog.duplicateditem.title|str_shorten_text:80:true}</a>
                <div id="{$blog.duplicateditem.id}_duplicatedblog" class="detail hidden">{$blog.duplicateditem.html|clean_html|safe}</div>
            </div>
            {/if}
            {if $blog.existingitems}
            <div class="existingblogs">
                <strong>{str tag=existingblogs section=artefact.blog}:</strong>
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
                <input id="decision_{$blog.id}_{$opt}" class="blogdecision" id="{$blog.id}" type="radio" name="decision_{$blog.id}" value="{$opt}"{if $blog.decision == $opt} checked="checked"{/if}>
                <label for="decision_{$blog.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$blog.title})</span></label><br>
                {/if}
            {/foreach}
        </div>
        <div class="cb"></div>
    </div>
    <div id="{$blog.id}_posts" class="indent2 hidden">
    {foreach from=$blog.entryposts item=post}
        <div id="posttitle_{$post.id}" class="{cycle name=rows values='r0,r1'} listrow {if $post.published}published{else}draft{/if}">
            <div class="importcolumn importcolumn1">
                <h4 class="title">
                    {if $post.description}<a class="posttitle" href="" id="{$post.id}">{/if}
                    {$post.title|str_shorten_text:80:true}
                    {if $post.description}</a>{/if}
                </h4>
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
                    <div id="postfiles">
                       <table class="cb attachments fullwidth">
                            <thead class="expandable-head">
                                <tr>
                                    <td>
                                        <a class="showpostfiles toggle expandable" id="{$blog.id}_{$post.id}" href="">{str tag=attachedfiles section=artefact.blog}</a>
                                        <span class="fr">
                                            <img class="fl" src="{theme_image_url filename='attachment'}" alt="{str tag=Attachments section=artefact.resume}">
                                            {$post.files|count}
                                        </span>
                                    </td>
                                </tr>
                            </thead>
                            <tbody id="{$blog.id}_{$post.id}_postfiles" class="expandable-body hidden">
                                {foreach from=$post.files item=file}
                                    <tr class="{cycle values='r1,r0'}">
                                        <td><h3 class="title">{$file.title}</h3>
                                        <div class="detail">{$file.description}</div></td>
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
                    <strong>{str tag=duplicatedpost section=artefact.blog}:</strong> <a class="showduplicatedpost" href="" id="{$post.duplicateditem.id}">{$post.duplicateditem.title|str_shorten_text:80:true}</a>
                    <div id="{$post.duplicateditem.id}_duplicatedpost" class="detail hidden">{$post.duplicateditem.html|clean_html|safe}</div>
                </div>
                {/if}
                {if $post.existingitems}
                <div class="existingposts">
                    <strong>{str tag=existingposts section=artefact.blog}:</strong>
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
                    <input id="decision_{$post.id}_{$opt}" class="postdecision" type="radio" name="decision_{$post.id}" value="{$opt}"{if $post.decision == $opt} checked="checked"{/if}>
                    <label for="decision_{$post.id}_{$opt}">{$displayopt}<span class="accessible-hidden">({$post.title})</span></label><br>
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
<script type="application/javascript">
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
        jQuery("a.showpostfiles").click(function(e) {
            e.preventDefault();
            jQuery("#" + this.id + "_postfiles").toggleClass("hidden");
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

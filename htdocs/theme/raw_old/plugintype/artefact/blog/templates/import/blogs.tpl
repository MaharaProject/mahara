{if $entryblogs}
<div class="section-import">
    <h2>{str tag=Blogs section=artefact.blog}</h2>
    {foreach from=$entryblogs item=blog}
    <div class="list-group-item">
        <div id="entryblog-{$blog.id}" class="row">
            <div class="col-md-8">
                <h3 class="title list-group-item-heading">
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
                    <strong>{str tag=blogpost section=artefact.blog}:</strong>
                    {str tag=nposts section=artefact.blog arg1=count($blog.entryposts)}
                </div>
                <!-- TODO Display existing journals and jounrnal count with section title -->
                <!-- {if $blog.existingitems}
                <div class="existingblogs">
                    <strong>{str tag=existingblogs section=artefact.blog}</strong>
                    <span>({count($blog.existingitems)})</span>
                </div>
                {/if} -->
                {if $blog.duplicateditem}
                <div class="duplicatedblog">
                    <strong class="text-warning">{str tag=duplicatedblog section=artefact.blog}</strong>
                </div>
                {/if}
            </div>
            <div class="col-md4">
            {foreach from=$displaydecisions key=opt item=displayopt}
                {if !$blog.disabled[$opt]}
                <label for="decision_{$blog.id}_{$opt}">
                    <input id="decision_{$blog.id}_{$opt}" class="blogdecision" id="{$blog.id}" type="radio" name="decision_{$blog.id}" value="{$opt}"{if $blog.decision == $opt} checked="checked"{/if}>
                    {$displayopt}
                    <span class="accessible-hidden sr-only">({$blog.title})</span>
                </label>
                {/if}
            {/foreach}
            </div>
        </div>
        <div id="{$blog.id}_posts" class="posts list-group list-group-lite">
        {foreach from=$blog.entryposts item=post}
            <div id="posttitle_{$post.id}" class="{if $post.published} published{else} draft{/if} list-group-item">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="title list-group-item-heading text-inline">
                            {if $post.description}
                            <a class="posttitle" href="" id="{$post.id}">
                                {$post.title|str_shorten_text:80:true}
                            </a>
                            {else}
                                {$post.title|str_shorten_text:80:true}
                            {/if}
                        </h4>
                        <span id="poststatus{$post.id}" class="poststatus text-small text-midtone">
                            {if $post.published}
                                ({str tag=published section=artefact.blog})
                            {else}
                                ({str tag=draft section=artefact.blog})
                            {/if}
                        </span>
                        <div id="{$post.id}_desc" class="detail hidden text-small">
                            {$post.description|clean_html|safe}
                        </div>
                        <p id="postdetails_{$post.id}" class="postdetails text-small">
                            {str tag=postedon section=artefact.blog} {$post.ctime}
                        </p>
                        {if $post.files}
                        <div class="attachments">
                            <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>
                            <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                            <span class="metadata">({$post.files|count})</span>
                        </div>
                        {/if}
                        {if $post.duplicateditem}
                        <div class="duplicatedblog">
                            <strong class="text-warning">{str tag=duplicatedpost section=artefact.blog}</strong>
                        </div>
                        {/if}
<!--                         {if $post.existingitems}
                        <div class="existingposts">
                            <div class="existingblogs">
                                <strong>{str tag=existingposts section=artefact.blog}</strong>
                                <span>({count($post.existingitems)})</span>
                            </div>
                        </div>
                        {/if} -->
                    </div>
                    <div class="col-md-4">
                        {foreach from=$displaydecisions key=opt item=displayopt}
                            {if !$post.disabled[$opt]}
                            <label for="decision_{$post.id}_{$opt}">
                                <input id="decision_{$post.id}_{$opt}" class="postdecision" type="radio" name="decision_{$post.id}" value="{$opt}"{if $post.decision == $opt} checked="checked"{/if}>
                                {$displayopt}
                                <span class="accessible-hidden sr-only">({$post.title})</span>
                            </label>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            </div>
        {/foreach}
        </div>
    </div>
    {/foreach}
</div>
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

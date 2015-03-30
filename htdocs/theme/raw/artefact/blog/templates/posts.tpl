{foreach from=$posts item=post}
    <div id="posttitle_{$post->id}" class="{if $post->published}published{else}draft{/if}">
        <div class="fr">
            <span id="poststatus{$post->id}" class="poststatus">
                {if $post->published}
                    {str tag=published section=artefact.blog}
                {else}
                    {str tag=draft section=artefact.blog}
                {/if}
            </span>
            <span id="changepoststatus{$post->id}" class="changepoststatus">
                {if !$post->locked}
                    {$post->changepoststatus|safe}
                {/if}
            </span>
            <span class="controls">
                {if $post->locked}
                    {str tag=submittedforassessment section=view}
                {else}
                    <form name="edit_{$post->id}" action="{$WWWROOT}artefact/blog/post.php">
                      <input type="hidden" name="id" value="{$post->id}">
                      <input type="image" src="{theme_image_url filename="btn_edit"}" alt="{str(tag=editspecific arg1=$post->title)|escape:html|safe}" title="{str tag=edit}">
                    </form>
                    {$post->delete|safe}
                {/if}
            </span>
        </div>
        <h2>{$post->title}</h2>
        <div id="postdetails_{$post->id}" class="postdetails postdate">
            {str tag=postedon section=artefact.blog} {$post->ctime}
        </div>
        <div id="postdescription_{$post->id}" class="postdescription">
            {$post->description|clean_html|safe}
        </div>
        {if $post->tags}<div id="posttags_{$post->id}" class="tags">{str tag=tags}: {list_tags owner=$post->author tags=$post->tags}</div>{/if}
        {if $post->files}
            <div id="postfiles_{$post->id}">
                <table class="cb attachments fullwidth">
                    <thead class="expandable-head">
                        <tr>
                            <td colspan="2">
                                <a class="toggle" href="#">{str tag=attachedfiles section=artefact.blog}</a>
                                <span class="fr">
                                    <img class="fl" src="{theme_image_url filename='attachment'}" alt="{str tag=Attachments section=artefact.resume}">
                                    {$post->files|count}
                                </span>
                            </td>
                        </tr>
                    </thead>
                    <tbody class="expandable-body">
                        {foreach from=$post->files item=file}
                            <tr class="{cycle values='r1,r0'}">
                                <td class="icon-container"><img src="{$file->icon}" alt=""></td>
                                <td><h3 class="title"><a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}">{$file->title}</a> <span class="description">({$file->size|display_size})</span></h3>
                                <div class="detail">{$file->description}</div></td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>
{/foreach}

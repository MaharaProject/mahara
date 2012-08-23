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
                      <input type="image" src="{theme_url filename="images/edit.gif"}" title="{str tag=edit}">
                    </form>
                    {$post->delete|safe}
                {/if}
            </span>
        </div>
        <h1 class="posttitle">{$post->title}</h1>
        <div id="postdescription_{$post->id}" class="postdescription">
            {$post->description|clean_html|safe}
        </div>
        {if $post->files}
            <div id="postfiles_{$post->id}">
                <table class="attachments fullwidth">
                    <col width="5%">
                    <col width="40%">
                    <col width="55%">
                    <tbody>
                        <tr><th colspan=3>{str tag=attachedfiles section=artefact.blog}</th></tr>
                        {foreach from=$post->files item=file}
                            <tr class="{cycle values='r1,r0'}">
                                <td><img src="{$file->icon}" alt=""></td>
                                <td class="valign"><a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}">{$file->title}</a></td>
                                <td class="valign">{$file->description}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}
        <div id="postdetails_{$post->id}" class="postdetails">
            {str tag=postedon section=artefact.blog} {$post->ctime}
        </div>
    </div>
{/foreach}

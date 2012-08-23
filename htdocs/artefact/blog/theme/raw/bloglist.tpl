{foreach from=$blogs->data item=blog}
    <tr class="{cycle name=rows values='r0,r1'}">
        <td colspan="2">
            <div class="fr">
                <span class="entries"><a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->postcount}{if $blog->postcount == 1}{str tag=post section=artefact.blog}{else}{str tag=posts section=artefact.blog}{/if}</a></span>
                <span class="newentry"><a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn">{str tag=addpost section=artefact.blog}</a></span>
                <span class="btns2">
                    {if $blog->locked}
                        <span class="s dull">{str tag=submittedforassessment section=view}</span>
                    {else}
                        <a href="{$WWWROOT}artefact/blog/settings/?id={$blog->id}" title="{str tag=settings}"><img src="{theme_url filename='images/manage.gif'}" alt="{str tag=settings}"></a>
                            {$blog->deleteform|safe}
                    {/if}
                </span>
            </div>
            <h4><a href="{$WWWROOT}artefact/blog/view/?id={$blog->id}">{$blog->title}</a></h4>
            <div id="blogdesc">{$blog->description|clean_html|safe}</div>
        </td>
    </tr>
{/foreach}

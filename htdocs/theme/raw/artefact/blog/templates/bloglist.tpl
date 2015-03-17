{foreach from=$blogs->data item=blog}
    <div class="{cycle name=rows values='r0,r1'} listrow">
            <h3 class="title"><a href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">{$blog->title}</a></h3>
            <div class="fr nowrap">
                <span class="entries"><a href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">{str tag=nposts section=artefact.blog arg1=$blog->postcount}</a></span>
                <span class="newentry"><a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn">{str tag=addpost section=artefact.blog}</a></span>
                <span class="btns2">
                    {if $blog->locked}
                        <span class="s dull">{str tag=submittedforassessment section=view}</span>
                    {else}
                        <a href="{$WWWROOT}artefact/blog/settings/index.php?id={$blog->id}" title="{str tag=settings}"><img src="{theme_image_url filename='btn_configure'}" alt="{str(tag=settingsspecific arg1=$blog->title)|escape:html|safe}"></a>
                            {$blog->deleteform|safe}
                    {/if}
                </span>
            </div>
            <div id="blogdesc">{$blog->description|clean_html|safe}</div>
            <div class="cb"></div>
    </div>
{/foreach}

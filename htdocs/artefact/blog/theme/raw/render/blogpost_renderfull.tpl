{**
* This template displays a blog post.
*}
<div id="blogpost">
    {if $artefacttitle}<h3 class="title">{$artefacttitle|safe}</h3>{/if}
    {$artefactdescription|clean_html|safe}
    {if isset($attachments)}
        <table class="cb attachments fullwidth">
            <thead class="expandable-head">
                <tr>
                    <td colspan="2">
                        <a class="toggle" href="#">{str tag=attachedfiles section=artefact.blog}</a>
                        <span class="fr">
                            <img class="fl" alt="{str tag=attachments section=artefact.blog}" src="{theme_url images/attachment.png}">
                            {$attachments|count}
                        </span>
                    </td>
                </tr>
            </thead>
            <tbody class="expandable-body">
                {if $artefact->get('tags')}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}</div>{/if}
                {foreach from=$attachments item=item}
                    <tr class="{cycle values='r0,r1'}">
                        {if $icons}<td class="icon-container"><img src="{$item->iconpath}" alt=""></td>{/if}
                        <td>
                            <a href="{$item->viewpath}">{$item->title}</a> ({$item->size}) - <strong><a href="{$item->downloadpath}">{str tag=Download section=artefact.file}</a></strong>
                            <br>{$item->description}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
    <div class="postdetails">{$postedbyon}
        {if isset($commentcount) && $artefact->get('allowcomments')} | <a href="{$artefacturl}">{str tag=Comments section=artefact.comment} ({$commentcount})</a>{/if}
    </div>
    {if $license}
    <div class="postlicense">
        {$license|safe}
    </div>
    {/if}
</div>

<h3 class="title">{$title}</h3>
{if $tags}<p class="tags s"><strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}</p>{/if}
<p>{$description|clean_html|safe}</p>
{if isset($attachments)}
    <table class="cb attachments fullwidth">
        <thead class="expandable-head">
            <tr>
                <td colspan="2">
                    <a class="toggle" href="#">{str tag=attachedfiles section=artefact.blog}</a>
                    <span class="fr">
                        <img class="fl" src="{theme_image_url filename='attachment'}" alt="{str tag=attachments section=artefact.blog}">
                        {$attachments|count}
                    </span>
                </td>
            </tr>
        </thead>
        <tbody class="expandable-body">
            {foreach from=$attachments item=item}
                <tr class="{cycle values='r0,r1'}">
                {if $icons}<td class="icon-container"><img src="{$item->iconpath}" alt=""></td>{/if}
                <td><a href="{$item->viewpath}">{$item->title}</a>
                ({$item->size}) - <strong><a href="{$item->downloadpath}">{str tag=Download section=artefact.file}</a></strong>
                <br>{$item->description}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
{/if}
{if $license}
  <div class="artefactlicense">
    {$license|safe}
  </div>
{/if}
<div class="cb"></div>
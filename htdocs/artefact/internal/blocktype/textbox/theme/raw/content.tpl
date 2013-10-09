{$text|clean_html|safe}
{if isset($attachments)}
<table class="cb attachments fullwidth">
  <thead class="expandable-head">
    <tr class="toggle">
      <td {if $icons}colspan="2"{/if} class="toggle-padding"><strong>{str tag=attachedfiles section=artefact.blog}</strong>
      <span class="fr"><img class="fl" src="{theme_url filename='images/attachment.png'}" alt="{str tag=Attachments section=artefact.resume}">&nbsp;{$count}</span>
      </td>
    </tr>
  </thead>
  <tbody class="expandable-body">
    {foreach from=$attachments item=item}
    <tr class="{cycle values='r0,r1'}">
      {if $icons}<td class="iconcell"><img src="{$item->iconpath}" alt=""></td>{/if}
      <td><a href="{$item->viewpath}">{$item->title}</a> ({$item->size}) - <strong><a href="{$item->downloadpath}">{str tag=Download section=artefact.file}</a></strong>
      <br>{$item->description}</td>
    </tr>
    {/foreach}
  </tbody>
</table>
{if $artefact->get('tags')}<div class="tags">{str tag=tags}: {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}</div>{/if}
{/if}
{if $commentcount || $commentcount === 0}
<div class="comments">
  <a href="{$artefacturl}">{str tag=Comments section=artefact.comment} ({$commentcount})</a>
</div>
{/if}


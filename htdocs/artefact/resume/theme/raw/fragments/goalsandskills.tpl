{$description|clean_html|safe}
{if isset($attachments)}
<table class="cb attachments fullwidth">
  <thead class="expandable-head">
    <tr class="toggle">
      <td colspan="2" class="toggle-padding"><strong>{str tag=attachedfiles section=artefact.blog}</strong>
      <img class="fr" src="{theme_url filename='images/attachment.png'}" alt="{str tag=Attachments section=artefact.resume}">
      <span class="fr">{$count}&nbsp;</span>
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
{/if}
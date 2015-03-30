{$description|clean_html|safe}
{if isset($attachments)}
<table class="cb attachments fullwidth">
  <thead class="expandable-head">
    <tr>
      <td colspan="2"><a class="toggle" href="#">{str tag=attachedfiles section=artefact.blog}</a>
      <img class="fr" src="{theme_image_url filename='attachment'}" alt="{str tag=Attachments section=artefact.resume}">
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
<script type="application/javascript">
setupExpanders(jQuery('table.attachments'));
</script>
{/if}

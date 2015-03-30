{$text|clean_html|safe}
{if isset($attachments)}
<table class="cb attachments fullwidth" id="blockinstance-attachments-{$blockid}">
  <thead class="expandable-head">
    <tr>
        <td colspan="2">
            <a class="toggle" href="#">{str tag=attachedfiles section=artefact.blog}</a>
            <span class="fr"><img class="fl" src="{theme_image_url filename='attachment'}" alt="{str tag=Attachments section=artefact.resume}">&nbsp;{$count}</span>
        </td>
    </tr>
  </thead>
  <tbody class="expandable-body">
    {foreach from=$attachments item=item}
    <tr class="{cycle values='r0,r1'}">
      <td class="icon-container"><img src="{$item->iconpath}" alt=""></td>
      <td>
        <h3 class="title"><a href="{$item->viewpath}">{$item->title}</a> <span class="description">({$item->size}) - <a href="{$item->downloadpath}">{str tag=Download section=artefact.file}</a></span></h3>
        <div class="detail">{$item->description}</div>
      </td>
    </tr>
    {/foreach}
  </tbody>
</table>
{if $artefact->get('tags')}<div class="tags">{str tag=tags}: {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}</div>{/if}
{/if}
{if $commentcount || $commentcount === '0'}
{$comments|safe}
{/if}

<script type="application/javascript">
setupExpanders($j('#blockinstance-attachments-{$blockid}'));
</script>
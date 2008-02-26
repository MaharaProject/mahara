{**
 * This template displays a blog post.
 *}
<div>
  {if $artefacttitle}<h3>{$artefacttitle}</h3>{/if}
  <div>{$artefactdescription}</div>
  {if isset($attachments)}
  <table class="cb blogpost-attachments">
    <tbody>
      <tr><th colspan="2">{str tag=attachedfiles section=artefact.blog}</th></tr>
  {foreach from=$attachments item=item}
      <tr class="r{cycle values=1,0}">
        <td style="width: 22px;"><img src="{$item->iconpath|escape}" alt=""></td>
        <td><a href="{$item->viewpath|escape}">{$item->title}</a> ({$item->size}) - <strong><a href="{$item->downloadpath|escape}">{str tag=Download section=artefact.file}</a></strong>
        <br><strong>{$item->description|escape}</strong></td>
      </tr>
  {/foreach}
    </tbody>
  </table>
  {/if}
  <div class="postdetails">{$postedbyon}</div>
</div>

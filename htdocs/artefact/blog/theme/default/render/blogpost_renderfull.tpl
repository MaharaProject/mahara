{**
 * This template displays a blog post.
 *}
<div>
  {if $artefacttitle}<h3>{$artefacttitle}</h3>{/if}
  <div>{$artefactdescription}</div>
  {if isset($attachments)}
  <table class="cb">
    <tbody>
      <tr><th colspan="2">{str tag=attachedfiles section=artefact.blog}:</th></tr>
  {foreach from=$attachments item=item}
      <tr>
        <td style="width: 22px;"><img src="{$item->iconpath|escape}" alt=""></td>
        <td><a href="{$item->viewpath|escape}">{$item->title}</a> ({$item->size}) - <strong><a href="{$item->downloadpath|escape}">Download</a></strong>
        <br><strong>{$item->description|escape}</strong></td>
      </tr>
  {/foreach}
    </tbody>
  </table>
  {/if}
  <div>{$postedbyon}</div>
</div>

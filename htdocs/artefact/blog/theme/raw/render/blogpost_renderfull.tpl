{**
 * This template displays a blog post.
 *}
<div>
  {if $artefacttitle}<h3>{$artefacttitle}</h3>{/if}
  <div>{$artefactdescription}</div>
  {if isset($attachments)}
  <table class="cb attachments fullwidth">
    <tbody>
      <tr><th colspan="2">{str tag=attachedfiles section=artefact.blog}:</th></tr>
  {if $artefact->get('tags')}<div class="tags">{str tag=tags}: {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}</div>{/if}
  {foreach from=$attachments item=item}
      <tr class="{cycle values='r0,r1'}">
        {if $icons}<td style="width: 22px;"><img src="{$item->iconpath|escape}" alt=""></td>{/if}
        <td><a href="{$item->viewpath|escape}">{$item->title|escape}</a> ({$item->size|escape}) - <strong><a href="{$item->downloadpath|escape}">{str tag=Download section=artefact.file}</a></strong>
        <br>{$item->description|escape}</td>
      </tr>
  {/foreach}
    </tbody>
  </table>
  {/if}
  <div class="postdetails">{$postedbyon|escape}</div>
</div>

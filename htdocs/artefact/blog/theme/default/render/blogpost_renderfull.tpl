{**
 * This template displays a blog post.
 *}
<div>
  <h3>{$artefacttitle}</h3>
  <div>{$artefact->get('description')}</div>
  {if isset($attachments)}
  <table>
    <tbody>
      <tr><th>{str tag=attachedfiles section=artefact.blog}:</th></tr>
  {foreach from=$attachments item=item}
      <tr>
         <td>{$item->content}</td>
      </tr>
  {/foreach}
    </tbody>
  </table>
  {/if}
  <div>{$postedbyon}</div>
</div>

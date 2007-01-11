<div>
  <h3>{$artefact->get('title')|escape}</h3>
  <div>{$artefact->get('description')}</div>
  <div>
  {if isset($children)}
    {str tag=contents section=artefact.file}:
    <table><tbody>
    {foreach from=$children item=child}
      <tr><td>{$child->title}</td><td>{$child->description}</td></tr>
    {/foreach}
    </tbody></table>
  {/if}
  </div>
</div>

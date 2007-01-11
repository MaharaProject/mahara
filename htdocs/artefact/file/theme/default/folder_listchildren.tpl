<div>
  {if isset($children)}
    <table>
    <tbody>
    {foreach from=$children item=child}
      <tr><td>{$child->title}</td><td>{$child->description}</td></tr>
    {/foreach}
    </tbody></table>
  {else}
    {str tag=empty section=artefact.file}
  {/if}
</div>

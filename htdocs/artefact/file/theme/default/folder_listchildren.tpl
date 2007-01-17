<div>
  {if empty($children)}
    {str tag=emptyfolder section=artefact.file}
  {else}
    <table>
    <tbody>
    {foreach from=$children item=child}
      <tr class="{cycle values=r1,r0}"><td>{$child->title}</td><td>{$child->description}</td></tr>
    {/foreach}
    </tbody></table>
  {/if}
</div>

<div>
  {if isset($children)}
    <table>
    <tbody>
    {foreach from=$children item=child}
      <tr class="{cycle values=r1,r0}"><td>{$child}</td></tr>
    {/foreach}
    </tbody></table>
  {else}
    {str tag=emptyfolder section=artefact.file}
  {/if}
</div>

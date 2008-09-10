<table class="templateresults tablerenderer">
  <thead>
{if ($ownername)}<tr><td colspan="3">{$ownername}</td></tr>{/if}
    <tr>
      <th>{str tag=name}</th>
      <th>{str tag=Owner section=view}</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
{if !empty($results)}
{foreach from=$results item=row}
    <tr class="r{cycle values=0,1}">
      <td>
        <a class="viewlink" href="{$WWWROOT}view/view.php?id={$row.id|escape}" target="_blank">{$row.title|escape}</a>
      </td>
      <td>
        {$row.sharedby|escape}
      </td>
      <td>
        {$row.form}
      </td>
    </tr>
{/foreach}
{else}
    <tr><td colspan="3">{str tag="nocopyableviewsfound" section=view}</td></tr>
{/if}
  </tbody>
</table>

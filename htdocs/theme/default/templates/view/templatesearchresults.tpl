<table class="templateresults tablerenderer">
  <thead>
{if ($ownername)}
    <tr><th colspan="3">{$ownername}</th></tr>
{else}
    <tr>
      <th>{str tag=name}</th>
      <th>{str tag=Owner section=view}</th>
      <th></th>
    </tr>
{/if}
  </thead>
  <tbody>
{if !empty($results)}
{foreach from=$results item=row}
    <tr class="r{cycle values=0,1}">
      <td{if ($ownername)} colspan="2"{/if}>
        <a class="viewlink" href="{$WWWROOT}view/view.php?id={$row.id|escape}" target="_blank">{$row.title|escape}</a>
      </td>
{if (!$ownername)}
      <td>{$row.sharedby|escape}</td>
{/if}
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

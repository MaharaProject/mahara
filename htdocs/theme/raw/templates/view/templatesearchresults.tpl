<table class="templateresults tablerenderer fullwidth">
  <thead>
    <tr>
      <th>{str tag=name}</th>
      <th>{str tag=Owner section=view}</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
{if $results}
{foreach from=$results item=row}
    <tr class="r{cycle values='0,1'}">
      <td>
        <a class="viewlink" href="{$WWWROOT}view/view.php?id={$row.id|escape}" target="_blank">{$row.title|escape}</a>
      </td>
{if $row.institution}
      <td>{$row.sharedby|escape}</td>
{elseif $row.group}
      <td><a class="grouplink" href="{$WWWROOT}group/view.php?id={$row.group|escape}" target="_blank">{$row.sharedby|escape}</a></td>
{elseif $row.owner}
      <td>
        <img src="{$WWWROOT}thumb.php?type=profileicon&maxwidth=20&maxheight=20&id={$row.owner|escape}" />
        <a class="userlink" href="{$WWWROOT}user/view.php?id={$row.owner|escape}" target="_blank">{$row.sharedby|escape}</a>
      </td>
{else}
      <td>-</td>
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

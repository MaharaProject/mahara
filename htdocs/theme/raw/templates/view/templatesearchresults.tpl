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
    <tr class="{cycle values='r0,r1'}">
      <td>
        <a class="viewlink" href="{$WWWROOT}view/view.php?id={$row.id}" target="_blank">{$row.title}</a>
      </td>
{if $row.institution}
      <td>{$row.sharedby}</td>
{elseif $row.group}
      <td><a class="grouplink" href="{$WWWROOT}group/view.php?id={$row.group}" target="_blank">{$row.sharedby}</a></td>
{elseif $row.owner}
      <td>
        <img src="{profile_icon_url user=$row.user maxwidth=20 maxheight=20}" alt="">
        <a class="userlink" href="{$WWWROOT}user/view.php?id={$row.owner}" target="_blank">{$row.sharedby}</a>
      </td>
{else}
      <td>-</td>
{/if}
      <td>
        {$row.form|safe}
      </td>
    </tr>
{/foreach}
{else}
    <tr><td colspan="3">{str tag="nocopyableviewsfound" section=view}</td></tr>
{/if}
  </tbody>
</table>

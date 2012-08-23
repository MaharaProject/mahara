<table class="templateresults tablerenderer fullwidth">
  <thead>
    <tr>
      <th class="collectiontitle">{str tag=collectiontitle section=collection}</th>
      <th class="viewname">{str tag=viewname section=view}</th>
      <th class="ownericon">{str tag=Owner section=view}</th>
      <th class="right"></th>
    </tr>
  </thead>
  <tbody>
{if $results}
{foreach from=$results item=row}
    <tr class="{cycle values='r0,r1'}">
      <td class="collectiontitle">
{if $row.collid}
        <strong><a class="collectionlink" href="{$WWWROOT}view/view.php?id={$row.id}" target="_blank">{$row.name}</a></strong>
{/if}
      </td>
      <td class="viewname">
        <strong><a class="viewlink" href="{$WWWROOT}view/view.php?id={$row.id}" target="_blank">{$row.title}</a></strong>
      </td>
{if $row.institution}
      <td class="owner s">{$row.sharedby}</td>
{elseif $row.group}
      <td class="owner s"><a class="grouplink" href="{group_homepage_url($row.groupdata, true, true)}" target="_blank">{$row.sharedby}</a></td>
{elseif $row.owner}
      <td class="ownericon s">
        <a class="userlink" href="{profile_url($row.user, true, true)}" target="_blank">
           <span class="profile-icon-container"><img src="{profile_icon_url user=$row.user maxwidth=20 maxheight=20}" alt=""></span>
           {$row.sharedby}
        </a>
      </td>
{else}
      <td class="owner s">-</td>
{/if}
      <td class="right s">
        {$row.form|safe}
      </td>
    </tr>
{/foreach}
{else}
    <tr><td colspan="3">{str tag="nocopyableviewsfound" section=view}</td></tr>
{/if}
  </tbody>
</table>

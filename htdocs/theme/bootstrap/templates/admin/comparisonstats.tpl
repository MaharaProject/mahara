{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    <td><a href="users/statistics.php?institution={$item->name}&type=registration">{$item->displayname}</a></td>
    <td><a href="users/statistics.php?institution={$item->name}&type=historical&field=count_members">{$item->count_members}</td>
    <td><a href="users/statistics.php?institution={$item->name}&type=historical&field=count_views">{$item->count_views}</td>
    <td><a href="users/statistics.php?institution={$item->name}&type=historical&field=count_blocks">{$item->count_blocks}</td>
    <td><a href="users/statistics.php?institution={$item->name}&type=historical&field=count_artefacts">{$item->count_artefacts}</td>
    <td><a href="users/statistics.php?institution={$item->name}&type=historical&field=count_interaction_forum_post">{$item->count_interaction_forum_post}</td>
  </tr>
{/foreach}


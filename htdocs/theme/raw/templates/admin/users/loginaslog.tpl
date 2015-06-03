{if $log}
<table class="fullwidth">
  <thead>
    <tr>
      <th>{str tag=masqueradee section=admin}</th>
      <th>{str tag=masqueradereason section=admin}</th>
      <th>{str tag=masquerader section=admin}</th>
      <th>{str tag=masqueradetime section=admin}</th>
    </tr>
  </thead>
  <tbody>
  {foreach from=$log item=l}
    <tr class="{cycle values='r0,r1'}">
      <td><a href="{profile_url($l->usr)}">{$l->usrname}</a></td>
      <td>{$l->data->reason}</a></td>
      <td><a href="{profile_url($l->realusr)}">{$l->realusrname}</a></td>
      <td>{$l->data->when}</a></td>
    </tr>
  {/foreach}
  </tbody>
</table>
{else}
<p>{str tag=nomasquerades section=admin}</p>
{/if}
{if $note}
<p>{$note|safe}</p>
{/if}

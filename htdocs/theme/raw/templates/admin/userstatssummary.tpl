<h3>{str tag=youraverageuser section=admin}</h3>
<ul>
  <li>{$data.strmaxfriends}</li>
  <li>{$data.strmaxviews}</li>
  <li>{$data.strmaxgroups}</li>
  <li>{$data.strmaxquotaused}</li>
</ul>
{if $data.institutions}
  <img src="{$data.institutions}" alt="" />
{/if}



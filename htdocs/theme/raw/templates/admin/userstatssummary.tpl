<h3>{str tag=youraverageuser section=admin}</h3>
<ul>
  <li>{$data.strmaxfriends|safe}</li>
  <li>{$data.strmaxviews|safe}</li>
  <li>{$data.strmaxgroups|safe}</li>
  <li>{$data.strmaxquotaused|safe}</li>
</ul>
{if $data.institutions}
  <img src="{$data.institutions}" alt="" />
{/if}

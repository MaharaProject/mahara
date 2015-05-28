<h3>{str tag=youraverageuser section=admin}</h3>
<ul class="list-group unstyled pull-left">
  <li class="list-group-item">{$data.strmaxfriends|safe}</li>
  <li class="list-group-item">{$data.strmaxviews|safe}</li>
  <li class="list-group-item">{$data.strmaxgroups|safe}</li>
  <li class="list-group-item">{$data.strmaxquotaused|safe}</li>
</ul>
{if $data.institutions}
  <img src="{$data.institutions}" alt="" class="pull-right" />
{/if}

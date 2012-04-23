{include file='header.tpl'}

<div>{str tag=cleanurlsdescription section=admin}</div>

{if $cleanurls}
<h4>{str tag=cleanurlsettings section=admin}</h4>
<table>
  {foreach from=$cleanurlconfig key=key item=item}
  <tr><td>$cfg->{$key}:</td><td>{$item}</td></tr>
  {/foreach}
</table>
<hr>
<div>{$regenerateform|safe}</div>
{/if}

{include file='footer.tpl'}


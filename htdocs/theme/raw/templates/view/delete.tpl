{include file="header.tpl"}
<div class="message delete">
  <p>{str tag="deleteviewconfirm" section="view" arg1=$WWWROOT}</p>
  {$form|safe}
  <p>{str tag="deleteviewconfirmnote" section="view"}</p>
</div>
{include file="footer.tpl"}

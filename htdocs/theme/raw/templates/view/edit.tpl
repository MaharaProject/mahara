{include file="header.tpl"}

<ul class="edit-view-tabs">
  <li>{$viewtitle}</li>
  <li><a class="current-tab" href="{$WWWROOT}view/edit.php?id={$viewid}">{str tag=edittitleanddescription section=view}</a></li>
  <li><a href="{$WWWROOT}view/blocks.php?id={$viewid}">{str tag=editcontent section=view}</a></li>
</ul>
<div class="rel">
			{$editview|safe}
</div>

{include file="footer.tpl"}

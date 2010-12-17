{include file="header.tpl"}

 <h1>{$viewtitle}</h1>
<ul class="in-page-tabs edit-view-tabs">
  <li><a class="current-tab" href="{$WWWROOT}view/edit.php?id={$viewid}">{str tag=edittitleanddescription section=view}</a></li>
  <li><a href="{$WWWROOT}view/blocks.php?id={$viewid}">{str tag=editcontent section=view}</a></li>
</ul>
<div class="subpage rel">
			{$editview|safe}
</div>

{include file="footer.tpl"}

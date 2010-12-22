{include file="header.tpl"}

{if $isadmin}
<div class="grouphome-admincontrol">
  <a href="{$WWWROOT}group/edit.php?id={$GROUP->id}" title="{str tag=editgroup section=group}"><img src="{theme_url filename="images/edit.gif"}"></a>
  <a href="{$WWWROOT}group/delete.php?id={$GROUP->id}" title="{str tag=deletegroup1 section=group}"><img src="{theme_url filename="images/icon_close.gif"}"></a>
</div>
{/if}

{if $GROUP->description}
	<div class="groupdescription">{$GROUP->description|clean_html|safe}</div>
{/if}

<div class="grouphomepage">
{$viewcontent|safe}
</div>
<div class="cb"></div>

{include file="footer.tpl"}

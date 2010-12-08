{include file="header.tpl"}

{if $isadmin}
<div class="grouphome-admincontrol">
  <a href="{$WWWROOT}group/edit.php?id={$group->id}" class="btn-big-edit" title="{str tag=editgroup section=group}"></a>
  <a href="{$WWWROOT}group/delete.php?id={$group->id}" class="btn-big-del" title="{str tag=deletegroup1 section=group}"></a>
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

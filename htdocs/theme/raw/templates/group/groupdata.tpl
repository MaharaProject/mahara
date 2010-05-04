{auto_escape off}
<div class="preview-group">
  <h3>{$group->name|escape}</h3>
  {if $group->description}<p id="group-description">{$group->description|escape}</p> {/if}
  <div class="group-info">{include file="group/info.tpl"}</div>
</div>
{/auto_escape}

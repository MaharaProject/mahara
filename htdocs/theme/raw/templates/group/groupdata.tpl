<div class="preview-group">
  <h3>{$group->name}</h3>
  {if $group->description}<p id="group-description">{$group->description|clean_html|safe}</p> {/if}
  <div class="group-info">{include file="group/info.tpl"}</div>
</div>

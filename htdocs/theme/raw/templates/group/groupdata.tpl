<div class="preview-group">
  <h2>{$group->name}</h2>
  {if $group->description}<p id="group-description">{$group->description|clean_html|safe}</p> {/if}
  <div class="group-info">{include file="group/info.tpl"}</div>
</div>

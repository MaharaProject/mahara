{include file="header.tpl"}

<p>{str tag=userreportsdescription section=admin}</p>

<form id="report" method="post">
  <select id="users" class="hidden" multiple="multiple" name="users[]">
  {foreach from=$users key=id item=item}
    <option selected="selected" value="{$id}">{$id}</option>
  {/foreach}
  </select>

  <div class="tabswrap"><ul class="in-page-tabs">
  {foreach from=$tabs item=tab}
    <li {if $tab.selected} class="current-tab"{/if}>
      <button type="submit" class="linkbtn{if $tab.selected} current-tab{/if}" name="report:{$tab.id}" value="{$tab.name}" />
        {$tab.name}<span class="accessible-hidden">({str tag=tab}{if $tab.selected} {str tag=selected}{/if})</span>
      </button>
    </li>
  {/foreach}
  </ul></div>
</form>

<div class="subpage">
  {if $csv}
  <div class="fr">
    <span class="bulkaction-title">{str tag=exportusersascsv section=admin}:</span>
    <a href="{$WWWROOT}download.php" target="_blank">{str tag=Download section=admin} <span class="accessible-hidden">{str tag=downloadusersascsv section=admin}</span></a>
  </div>
  {/if}
  <h2>{str tag=selectedusers section=admin} ({count($users)})</h2>

  {$userlisthtml|safe}
</div>

{include file="footer.tpl"}

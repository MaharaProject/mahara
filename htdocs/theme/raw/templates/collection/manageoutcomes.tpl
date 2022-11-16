{include file="header.tpl"}
<div class="form-group requiredmarkerdesc">Fields marked by '*' are required.</div>
<div id="outcome_forms" class="outcome-form-list">
  {foreach from=$outcomeforms item=form key=i}
    {if $i>0}
      <span class="delete-outcome" style="float: right; margin-top: 8px">
        <a href="#" title={str tag=delete}>
          <span role="presentation" class="icon icon-trash-alt text-danger"></span>
        </a>
      </span>
    {/if}
    {$form|safe}
  {/foreach}
</div>
<div class="outcome-form-section">
  <a id="add_outcome" class="add-outcome-link" href="#">+ {get_string('addoutcomelink', 'collection')}</a>
</div>

<div id="outcome_buttons_container" class="outcome-form-section">
  <button id="submit_save" class="btn btn-primary submitcancel submit" type="submit" d>
    {str tag=save}
  </button>
  <button id="submit_cancel" class="btn submitcancel cancel" type="submit" data-url="{$cancelredirecturl}">
    {str tag=cancel}
  </button>
</div>

{include file="footer.tpl"}

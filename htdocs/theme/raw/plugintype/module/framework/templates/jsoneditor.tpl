{include file="header.tpl"}

<div id="delete_submit_container" class=" default submitcancel form-group">
    <button type="submit" class="btn-primary submitcancel submit btn" name="submit" tabindex="0">
        {str tag='save'}
    </button>
    <button id='preview' class='btn-default button btn'>{str tag="Preview" section="view"}</button>
    <input type="submit" id='cancel' class="btn-default submitcancel cancel" name="cancel_submit" tabindex="0" value="{str tag='cancel'}">
</div>
<div id='edit_framework' class="select form-group"><label for=edit>{str tag="editsavedframework" section="module.framework"}</label>
    <select id='edit' class="select form-control">{foreach from=$fw_edit key=fw_edk item=fw_ed}<option value={$fw_edk}>{$fw_ed}</option>{/foreach}</select>
        <div class="description">
        {$edit_desc}</div>
</div>
<div id='copy_framework' class="select form-group"><label for="copy">{str tag="copyexistingframework" section="module.framework"}</label>
    <select id='copy' class="select form-control">{foreach from=$fw key=fw_k item=fw}<option value={$fw_k}>{$fw}</option>{/foreach}</select>
        <div class="description">
        {$copy_desc}</div>
</div>
<span id='valid_indicator'></span>
{* the json editor form is all in 'editor_holder' *}
<div id='editor_holder'></div>
<div id="delete_submit_container_end" class=" default submitcancel form-group">
    <button type="submit" class="btn-primary submitcancel submit btn" name="submit" tabindex="0">
        {str tag='save'}
    </button>
    <input type="submit" id='cancel_end' class="btn-default submitcancel cancel" name="cancel_submit" tabindex="0" value="{str tag='cancel'}">
</div>


{include file="footer.tpl"}
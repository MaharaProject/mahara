{include file="header.tpl"}

<div class="center">
    <p class="alert alert-warning">{str tag=changeviewtheme section=view}</p>
    <form action="{$formurl}" method="post" class="pieform form-inline">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="sesskey" value="{$SESSKEY}">
        <div class="select form-group">
            <label for="viewtheme-select">{str tag=theme}: </label>
            <span class="picker">
                <select id="viewtheme-select" name="viewtheme" class="form-control select autofocus">
                    {foreach from=$viewthemes key=themeid item=themename}
                    <option value="{$themeid}"{if $themeid == $viewtheme} selected="selected" style="font-weight: bold;"{/if}>{$themename}</option>
                    {/foreach}
                </select>
            </span>
            <input class="submit btn btn-primary" type="submit" value="{str tag=submit}">
        </div>
    </form>
</div>

{include file="footer.tpl"}

{include file="header.tpl"}
<h1>{$maintitle}</h1>

<div class="center">
    <p>{str tag=changeviewtheme section=view}</p>
    <form action="{$formurl}" method="post">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="sesskey" value="{$SESSKEY}">
        <label for="viewtheme-select">{str tag=theme}: </label>
        <select id="viewtheme-select" name="viewtheme">
{foreach from=$viewthemes key=themeid item=themename}
            <option value="{$themeid}"{if $themeid == $viewtheme} selected="selected" style="font-weight: bold;"{/if}>{$themename}</option>
{/foreach}
        </select>
        <input class="submit btn btn-primary" type="submit" value="{str tag=submit}">
    </form>
</div>

{include file="footer.tpl"}

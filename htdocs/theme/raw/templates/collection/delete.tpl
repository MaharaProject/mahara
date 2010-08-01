{auto_escape on}
{include file="header.tpl"}
<div class="message">
<h3>{$subheading|escape}</h3>
<p>{$message}</p>
{$form|safe}
</div>
{include file="footer.tpl"}
{auto_escape off}

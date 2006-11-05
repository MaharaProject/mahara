{include file="header.tpl"}

{if !$USER}
<div class="sidebar">
    {$login_form}
</div>
{/if}

{include file="adminmenu.tpl"}

<div class="content">
    Content from database here
</div>

{include file="footer.tpl"}

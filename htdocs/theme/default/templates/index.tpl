{include file="header.tpl"}

{if !$USER}
<div class="sidebar" id="loginbox"></div>
{$login_form}
{/if}

{include file="adminmenu.tpl"}

<div class="content">
    {$page_content}
</div>

{include file="footer.tpl"}

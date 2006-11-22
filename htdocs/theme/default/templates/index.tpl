{include file="header.tpl"}

{if !$USER}
<div class="sidebar" id="loginbox">
<noscript><p>{str tag="javascriptnotenabled"}</p></noscript>
</div>
{$login_form}
{else}
{include file="searchbox.tpl"}
{/if}

{include file="adminmenu.tpl"}

<div class="content">
    {$page_content}
</div>

{include file="footer.tpl"}

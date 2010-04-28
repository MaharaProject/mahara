{include file="header.tpl"}
{$page_content|clean_html}
{if $dashboardview}
{include file="user/dashboard.tpl"}
{/if}
{include file="footer.tpl"}

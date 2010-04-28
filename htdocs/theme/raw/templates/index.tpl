{include file="header.tpl"}
{$page_content|clean_html}
{if !$USER->is_logged_in() || $USER->get('showhomeinfo')}
{include file="homeinfo.tpl"}
{/if}
{if $dashboardview}
{include file="user/dashboard.tpl"}
{/if}
{include file="footer.tpl"}

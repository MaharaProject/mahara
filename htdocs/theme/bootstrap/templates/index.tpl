{include file="header.tpl"}
{$page_content|clean_html|safe}
{if get_config('homepageinfo') && (!$USER->is_logged_in() || $USER->get_account_preference('showhomeinfo'))}
    {include file="homeinfo.tpl" url=$url}
{/if}
{if $dashboardview}
    <div class="rbuttons" id="editdashboard">
        <a class="btn" href="{$WWWROOT}view/blocks.php?id={$viewid}"><span  class="btn-edit">{str tag='editdashboard'}</span></a>
    </div>
    {include file="user/dashboard.tpl"}
{/if}
{include file="footer.tpl"}

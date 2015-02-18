{include file="header.tpl"}
{$page_content|clean_html|safe}
{if get_config('homepageinfo') && (!$USER->is_logged_in() || $USER->get_account_preference('showhomeinfo'))}
    {include file="homeinfo.tpl" url=$url}
{/if}
{if $dashboardview}
    {include file="user/dashboard.tpl"}
    <div class="align-right" id="editdashboard">
        <a class="btn btn-sm btn-success" href="{$WWWROOT}view/blocks.php?id={$viewid}"><span class="btn-edit"><span class="fa fa-pencil prs"> </span> {str tag='editdashboard'}</span></a>
    </div>
    
{/if}
{include file="footer.tpl"}

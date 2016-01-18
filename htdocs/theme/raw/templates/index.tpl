{include file="header.tpl"}
{$page_content|clean_html|safe}
{if get_config('homepageinfo') && (!$USER->is_logged_in() || $USER->get_account_preference('showhomeinfo'))}
    {include file="homeinfo.tpl" url=$url}
{/if}
{if $dashboardview}
    <div class="dashboard-editable">
        <div class="btn-top-right btn-group btn-group-top" id="editdashboard">
            <a class="btn btn-default" href="{$WWWROOT}view/blocks.php?id={$viewid}"><span class="btn-edit"><span class="icon icon-pencil left" role="presentation" aria-hidden="true"> </span> {str tag='editdashboard'}</span></a>
        </div>

        {include file="user/dashboard.tpl"}

    </div>
{/if}
{include file="footer.tpl"}

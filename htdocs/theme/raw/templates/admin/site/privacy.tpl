{include file="header.tpl"}
    {if $versionid && $version == $latestversion}
        <div class="lead">{str tag="privacypagedescription" section="admin"}</div>
            {if $pageeditform}
            <div class="col-md-9">
                <div class="panel panel-default">
                    <div class="panel-body">
                        {$pageeditform|safe}
                    </div>
                </div>
            </div>
            {/if}
        </div>
    {else}
    <div class="lead">{str tag="privacypagedescription" section="admin"}</div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default view-container">
                <div class="table-responsive">
                    <table id="adminstitutionslist" class="fullwidth table table-striped">
                        <thead>
                        <tr>
                            <th>{str tag="version" section="admin"}</th>
                            <th>{str tag="author" section="admin"}</th>
                            <th>{str tag="content" section="admin"}</th>
                            <th>{str tag="creationdate" section="admin"}</th>
                            <th><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
                        </tr>
                        </thead>
                        <tbody>
                            {foreach from=$results item=result key=key}
                                <tr>
                                    <td>{$result->version}</td>
                                    <td>{if $result->firstname === NULL}
                                            {str tag=default}
                                        {else}
                                            <a href="{$WWWROOT}user/view.php?id={$result->userid}">
                                                {$result->firstname} {$result->lastname}
                                            </a>
                                        {/if}
                                    </td>
                                    <td>{$result->content|truncate:100:"..."|htmlspecialchars_decode|strip_tags}</td>
                                    <td>{$result->ctime|date_format:'%d %b %Y %H:%M'}</td>
                                    <td class="control-buttons">
                                        {if $key === $latestprivacyid}
                                            <div class="btn-group">
                                                <a href="{$WWWROOT}admin/site/privacy.php?id={$result->id}" title="{str tag=editversion section='admin' arg1='$result->version'}" class="btn btn-default btn-xs">
                                                    <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                                                </a>
                                            </div>
                                        {else}
                                            <div class="btn-group">
                                                <a href="{$WWWROOT}admin/site/privacy.php?id={$result->id}" title="{str tag=viewversion section='admin' arg1='$result->version'}" class="btn btn-default btn-xs">
                                                    <span class="icon icon-eye icon-lg" role="presentation" aria-hidden="true"></span>
                                                </a>
                                            </div>
                                        {/if}
                                    </td>
                                </tr>
                                {if $result->version === $version}
                                <tr>
                                    <td colspan="5">
                                        <div>{str tag=versionfor section=admin arg1="$result->version"}</div>
                                        {$result->content|clean_html|safe}
                                    </td>
                                </tr>
                                {/if}
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}
{include file="footer.tpl"}

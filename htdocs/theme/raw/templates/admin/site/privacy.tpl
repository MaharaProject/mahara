{include file="header.tpl"}
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
                                    <td>{$result->ctime|date_format:'%d %b %Y %H:%I'}</td>
                                    <td class="control-buttons"></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}

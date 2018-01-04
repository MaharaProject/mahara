{include file="header.tpl"}
<div class="lead">{str tag="institutionprivacypagedescription" section="admin"}</div>
<div class="panel panel-default">
    <div class="last form-group collapsible-group">
        <fieldset class="pieform-fieldset last collapsible">
            <legend>
                <h4>
                    <a href="#dropdown" data-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                        {str tag="siteprivacystatement" section="admin"}
                        <span class="icon icon-chevron-down collapse-indicator right pull-right"> </span>
                    </a>
                </h4>
            </legend>
            <div class="fieldset-body collapse " id="dropdown">
                <span class="text-midtone pull-right">{$lastupdated}</span>
                <br>
                {$siteprivacycontent->content|safe}
            </div>
        </fieldset>
    </div>
</div>
{if $versionid !== null}
    <div class="panel panel-default">
        <div class="panel-body">
            {$pageeditform|safe}
        </div>
    </div>
{else}
    {if $privacies}
        <div class="panel panel-default">
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
                        {foreach from=$privacies item=result key=key}
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
                                <td class="control-buttons"></td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    {else}
        <div class="panel panel-default">
            <div id="institutionprivacylistcontainer">
                <div class="no-results">
                    {str tag="noinstitutionprivacy" section="admin"}
                    {str tag="addoneversionlink" section="admin" arg1=$href}
                </div>
            </div>
        </div>
    {/if}
{/if}
{include file="footer.tpl"}

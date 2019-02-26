{include file="header.tpl"}
{if $id !== null && !empty($usercontent)}
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#privacy" id="privacylink" role="tab" data-toggle="tab" aria-expanded="true" onclick="showTab('#privacy')">
                {str tag="privacy" section="admin"}
            </a>
        </li>
        <li role="presentation">
            <a href="#termsandconditions" id="termsandconditionslink" role="tab" data-toggle="tab" aria-expanded="false" onclick="showTab('#termsandconditions')">
                {str tag="termsandconditions" section="admin"}
            </a>
        </li>
    </ul>
    <br>
<div id="privacy-text" class="lead tab">{str tag="userprivacyagreements" section="admin"}</div>
<div id="termsandconditions-text" class="lead tab js-hidden">{str tag="usertermsagreements" section="admin"}</div>
<div class="card view-container">
    <div class="table-responsive">
        <table id="adminstitutionslist" class="fullwidth table table-striped">
            <thead>
            <tr>
                <th>{str tag="institution"}</th>
                <th>{str tag="consentdate" section="admin"}</th>
                <th>{str tag="consented" section="admin"}</th>
                <th>{str tag="version" section="admin"}</th>
                <th>{str tag="latest"}</th>
                <th><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
            </tr>
            </thead>
            <tbody id="privacy" class="tab">
                {foreach from=$usercontent item=content}
                    {if $content->type == 'privacy'}
                        <tr>
                            <td>
                                {if $content->institution == 'mahara'}{str tag="Site"}{else}{$content->displayname}{/if}
                            </td>
                            <td>
                                {$content->agreeddate|date_format:'%d %b %Y %H:%M'}
                            </td>
                            <td>
                                {if $content->agreed}{str tag="yes"}{else}{str tag="no"}{/if}
                            </td>
                            <td>
                                {$content->version|clean_html|safe}
                            </td>
                            <td>
                                {if $content->current}{str tag="yes"}{else}{str tag="no"}{/if}
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{$link}&activetab=privacy&versionid={$content->id}" title="{str tag=viewversion section='admin' arg1='$content->version'}" class="btn btn-secondary btn-sm">
                                        <span class="icon icon-eye icon-lg" role="presentation" aria-hidden="true"></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        {if $content->id == $versionid}
                        <tr>
                            <td colspan="6">
                                <div>
                                {if $content->type == 'privacy'}
                                    {str tag=privacyversionfor section=admin arg1="$content->version"}
                                {else}
                                    {str tag=termsversionfor section=admin arg1="$content->version"}
                                {/if}
                                </div>
                                {$content->content|clean_html|safe}
                            </td>
                        </tr>
                        {/if}
                    {/if}
                {/foreach}
            </tbody>
            <tbody id="termsandconditions" class="tab js-hidden">
                {foreach from=$usercontent item=content}
                    {if $content->type == 'termsandconditions'}
                        <tr>
                            <td>
                                {if $content->institution == 'mahara'}{str tag="Site"}{else}{$content->displayname}{/if}
                            </td>
                            <td>
                                {$content->agreeddate|date_format:'%d %b %Y %H:%M'}
                            </td>
                            <td>
                                {if $content->agreed}{str tag="yes"}{else}{str tag="no"}{/if}
                            </td>
                            <td>
                                {$content->version|clean_html|safe}
                            </td>
                            <td>
                                {if $content->current}{str tag="yes"}{else}{str tag="no"}{/if}
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{$link}&activetab=termsandconditions&versionid={$content->id}" title="{str tag=viewversion section='admin' arg1='$content->version'}" class="btn btn-secondary btn-sm">
                                        <span class="icon icon-eye icon-lg" role="presentation" aria-hidden="true"></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        {if $content->id == $versionid}
                        <tr>
                            <td colspan="6">
                                <div>
                                {if $content->type == 'termsandconditions'}
                                    {str tag=termsversionfor section=admin arg1="$content->version"}
                                {else}
                                    {str tag=privacyversionfor section=admin arg1="$content->version"}
                                {/if}
                                </div>
                                {$content->content|clean_html|safe}
                            </td>
                        </tr>
                        {/if}
                    {/if}
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
{/if}
{include file="footer.tpl"}
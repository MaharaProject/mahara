{include file="header.tpl"}
    {if $versionid && in_array($versionid, $latestVersions)}
        <div class="lead">
        {if $selectedtab == 'termsandconditions'}
            {str tag="termspagedescription" section="admin"}
        {else}
            {str tag="privacypagedescription" section="admin"}
        {/if}
        </div>
            {if $pageeditform}
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-body">
                        {$pageeditform|safe}
                    </div>
                </div>
            </div>
            {/if}
        </div>
    {else}
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#privacy" role="tab" data-toggle="tab" aria-expanded="true" onclick="showTab('#privacy')">
                {str tag="privacy" section="admin"}
            </a>
        </li>
        <li role="presentation">
            <a href="#termsandconditions" role="tab" data-toggle="tab" aria-expanded="false" onclick="showTab('#termsandconditions')">
                {str tag="termsandconditions" section="admin"}
            </a>
        </li>
    </ul>
    <br>
    <div id="privacy-text" class="lead tab">{str tag="privacypagedescription" section="admin"}</div>
    <div id="termsandconditions-text" class="lead tab js-hidden">{str tag="termspagedescription" section="admin"}</div>
        <div class="row">
            <div class="col-md-12">
                <div class="card view-container">
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
                            <tbody id="privacy" class="tab">
                                {foreach from=$results item=result}
                                    {if $result->type == 'privacy'}
                                        {include file="admin/site/privacytable.tpl"}
                                    {/if}
                                {/foreach}
                            </tbody>
                            <tbody id="termsandconditions" class="tab js-hidden">
                                {foreach from=$results item=result}
                                    {if $result->type == 'termsandconditions'}
                                        {include file="admin/site/privacytable.tpl"}
                                    {/if}
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
{/if}
{include file="footer.tpl"}

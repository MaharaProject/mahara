{include file="header.tpl"}
<script type="text/javascript">
    var types = '{$types}';
</script>
{if $versionid === null || !in_array($versionid, $latestVersions)}
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
{/if}
<div id="privacy-text" class="tab">
    <div class="card" id="privacyst">
        <div class="last form-group collapsible-group">
            <fieldset class="pieform-fieldset last collapsible">
                <legend>
                    <h4>
                        <a href="#dropdown-privacyst-{$sitecontent['privacy']->id}" data-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                            {str tag="siteprivacy" section="admin"}
                            <span class="icon icon-chevron-down collapse-indicator right float-right"> </span>
                        </a>
                    </h4>
                </legend>
                <div class="fieldset-body collapse" id="dropdown-privacyst-{$sitecontent['privacy']->id}">
                    <span class="text-midtone float-right">
                        {str tag="lastupdated" section="admin"} {$sitecontent['privacy']->ctime|date_format:'%d %B %Y %H:%M %p'}
                    </span>
                    <div class="last-updated-offset">
                        {$sitecontent['privacy']->content|safe}
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <div class="lead">{str tag="institutionprivacypagedescription" section="admin"}</div>
</div>
<div id="termsandconditions-text" class="tab">
    <div class="card" id="terms">
        <div class="last form-group collapsible-group">
            <fieldset class="pieform-fieldset last collapsible">
                <legend>
                    <h4>
                        <a href="#dropdown-terms-{$sitecontent['termsandconditions']->id}" data-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                            {str tag="sitetermsandconditions" section="admin"}
                            <span class="icon icon-chevron-down collapse-indicator right float-right"> </span>
                        </a>
                    </h4>
                </legend>
                <div class="fieldset-body collapse" id="dropdown-terms-{$sitecontent['termsandconditions']->id}">
                    <span class="text-midtone float-right">
                        {str tag="lastupdated" section="admin"} {$sitecontent['termsandconditions']->ctime|date_format:'%d %B %Y %H:%M %p'}
                    </span>
                    <div class="last-updated-offset">
                        {$sitecontent['termsandconditions']->content|safe}
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
    <div class="lead">{str tag="institutiontermspagedescription" section="admin"}</div>
</div>
{if $versionid !== null && in_array($versionid, $latestVersions)}
    <div class="card">
         <div class="card-body">
            {$pageeditform|safe}
        </div>
    </div>
{else}
    <div id="results" class="card">
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
    <div id="no-results" class="card js-hidden">
        <div id="institutionprivacylistcontainer">
            <div class="no-results ">
                <span id="no-privacy" class="nocontent">{str tag="noinstitutionprivacy" section="admin"}</span>
                <span id="no-termsandconditions" class="nocontent">{str tag="noinstitutionterms" section="admin"}</span>
                {str tag="addoneversionlink" section="admin" arg1=$href}
            </div>
        </div>
    </div>
{/if}
{include file="footer.tpl"}

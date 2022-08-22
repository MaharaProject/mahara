{include file="header.tpl"}
<script type="text/javascript">
    var types = '{$types}';
</script>
{if $versionid === null || !in_array($versionid, $latestVersions)}
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#privacy" role="tab" data-bs-toggle="tab" aria-expanded="true" onclick="showTab('#privacy')">
                {str tag="privacy" section="admin"}
            </a>
        </li>
        <li role="presentation">
            <a href="#termsandconditions" role="tab" data-bs-toggle="tab" aria-expanded="false" onclick="showTab('#termsandconditions')">
                {str tag="termsandconditions" section="admin"}
            </a>
        </li>
    </ul>
    <br>
{/if}
<div id="privacy-text" class="tab">
    <div class="card first last form-group collapsible-group" id="privacyst">
        <fieldset class="pieform-fieldset first last collapsible">
            <legend>
                <a href="#dropdown-privacyst-{$sitecontent['privacy']->id}" data-bs-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                    {str tag="siteprivacy" section="admin"}
                    <span class="icon icon-chevron-down collapse-indicator right float-end"> </span>
                </a>
            </legend>
            <div class="fieldset-body collapse" id="dropdown-privacyst-{$sitecontent['privacy']->id}">
                {$sitecontent['privacy']->content|safe}
                <span class="text-midtone text-small">
                    {str tag="lastupdated" section="admin"} {$sitecontent['privacy']->ctime|date_format:'%d %B %Y %H:%M %p'}
                </span>
            </div>
        </fieldset>
    </div>
    <div class="lead"><br />{str tag="institutionprivacypagedescription" section="admin"}</div>
</div>
<div id="termsandconditions-text" class="tab">
    <div class="card first last form-group collapsible-group" id="terms">
        <fieldset class="pieform-fieldset first last collapsible">
            <legend>
                <a href="#dropdown-terms-{$sitecontent['termsandconditions']->id}" data-bs-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                    {str tag="sitetermsandconditions" section="admin"}
                    <span class="icon icon-chevron-down collapse-indicator right float-end"> </span>
                </a>
            </legend>
            <div class="fieldset-body collapse" id="dropdown-terms-{$sitecontent['termsandconditions']->id}">
                {$sitecontent['termsandconditions']->content|safe}
                <span class="text-midtone text-small">
                    {str tag="lastupdated" section="admin"} {$sitecontent['termsandconditions']->ctime|date_format:'%d %B %Y %H:%M %p'}
                </span>
            </div>
        </fieldset>
    </div>
    <div class="lead"><br />{str tag="institutiontermspagedescription" section="admin"}</div>
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
                    <th><span class="accessible-hidden visually-hidden">{str tag=edit}</span></th>
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

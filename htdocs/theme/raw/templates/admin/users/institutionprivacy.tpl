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
<div class="panel panel-default">
    <div id="institutionprivacylistcontainer">
        {$data|safe}
    </div>
</div>
{include file="footer.tpl"}

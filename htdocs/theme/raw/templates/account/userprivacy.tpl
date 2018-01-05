{include file="header.tpl"}
<div class="lead">{str tag="userprivacypagedescription" section="admin"}</div>
{foreach from=$results item=result key=key}
    <div class="panel panel-default" id="{$result->id}" onclick="showPanel(this)">
        <div class="last form-group collapsible-group">
            <fieldset class="pieform-fieldset last collapsible">
                <legend>
                    <h4>
                        <a href="#dropdown" data-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                            {if $result->institution == 'mahara'}
                                {str tag="siteprivacystatement" section="admin"}
                            {else}
                                {str tag="institutionprivacystatement" section="admin"}
                            {/if}
                            <span class="icon icon-chevron-down collapse-indicator right pull-right"> </span>
                        </a>
                    </h4>
                </legend>
                    <div class="fieldset-body collapse" id="dropdown{$result->id}">
                        <span class="text-midtone pull-right">{str tag="lastupdated" section="admin"} {$result->ctime|date_format:'%d %B %Y %H:%M %p'}</span>
                        <br>
                        {$result->content|safe}
                    </div>
            </fieldset>
        </div>
    </div>
{/foreach}
{include file="footer.tpl"}

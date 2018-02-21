{include file="header.tpl"}
{foreach from=$sections key=k item=section}
    <div class="panel panel-default">
        <div class="last form-group collapsible-group">
            <fieldset class="pieform-fieldset collapsible">
                <legend>
                    <h4>
                        <a href="#dropdown{$k}" data-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                            {$section.title}
                            <span class="icon icon-chevron-down collapse-indicator right pull-right"></span>
                        </a>
                    </h4>
                </legend>
                <div class="fieldset-body collapse in" id="dropdown{$k}">
                    {$section.content|safe}
                </div>
            </fieldset>
        </div>
    </div>
{/foreach}
{include file="footer.tpl"}

{include file="header.tpl"}

<p>{$modifications|safe}</p>

{foreach from=$data item=item}
    <h3>{$item.title}</h3>
    <ol>
    <li>{$item.item1|safe}</li>
    <li>{$item.item2|safe}</li>
    <li>{$item.item3|safe}</li>
    </ol>

    <div class="form-group collapsible-group">
        <fieldset class="pieform-fieldset collapsible">
            <legend>
                <h4>
                    <a href="#example_{$item.id}" data-toggle="collapse" aria-expanded="true" class="collapsed">{str tag=example section=cookieconsent}
                        <span class="icon icon-chevron-down collapse-indicator right pull-right" role="presentation" aria-hidden="true"></span>
                    </a>
                </h4>
            </legend>
            <div id="example_{$item.id}" class="fieldset-body collapse">
                <p>{str tag=examplebefore section=cookieconsent}</p>
                {$item.code1|safe}
                {str tag=exampleafter section=cookieconsent}
                {$item.code2|safe}
            </div>
        </fieldset>
    </div>
    <div class="last form-group collapsible-group">
        <fieldset class="pieform-fieldset last collapsible">
            <legend>
                <h4>
                    <a href="#didnt_work_{$item.id}" data-toggle="collapse" aria-expanded="true" class="collapsed">{str tag=itdidntwork section=cookieconsent}
                        <span class="icon icon-chevron-down collapse-indicator right pull-right" role="presentation" aria-hidden="true"></span>
                    </a>
                </h4>
            </legend>
            <div id="didnt_work_{$item.id}" class="fieldset-body collapse">
                <p>{$item.help1|safe}</p>
                <p>{$item.help2|safe}</p>
            </div>
        </fieldset>
    </div>
{/foreach}

{include file="footer.tpl"}

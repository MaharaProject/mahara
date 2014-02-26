{include file="header.tpl"}
    <p>{$introtext1|safe}</p>
    <p>{$introtext2|safe}</p>
    <p><em>{$introtext3|safe}</em></p>
    <table class="cb fullwidth">
        <thead class="expandable-head">
            <tr>
                <td colspan="2">
                    <a class="toggle" href="#">{str tag=readfulltext1 section=cookieconsent}</a>
                </td>
            </tr>
        </thead>
        <tbody class="expandable-body">
            <tr class="r0" id="directive_2009136_container">
                <th>{str tag=directive2009136 section=cookieconsent}</th>
                <td>
                    {foreach from=$languages item=lang name=languages}
                    <a href="http://eur-lex.europa.eu/LexUriServ/LexUriServ.do?uri=OJ:L:2009:337:0011:0036:{$lang}:PDF" target="_blank" title="{str tag=readdirective$lang section=cookieconsent}">{$lang}</a>{if not  $dwoo.foreach.languages.last} | {/if}
                    {/foreach}
                </td>
            </tr>
        </tbody>
    </table>
    <p>{$introtext4|safe}</p>
    <p>{$introtext5|safe}</p>
    {$form|safe}
{include file="footer.tpl"}


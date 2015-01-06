{include file="header.tpl"}
            {if $noinstitutionsadmin}<p>{$noinstitutionsadmin|safe}</p>{/if}
            {if $noinstitutions}
                <p>{$noinstitutions}</p>
            {else}
                <p>{str tag=staticpagespagedescription section=admin}</p>
            {/if}
            {$pageeditform|safe}
{include file="footer.tpl"}


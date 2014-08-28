{include file="header.tpl"}
            <div id="progressbar-construct">
            <p>{str tag="profilecompletenessdesc" section="admin"}</p>
            <p>{str tag="profilecompletenesspreview" section="admin"}</p>
            {if !$enabled}
            <p>{str tag=progressbardisablednote section=admin args=$WWWROOT}</p>
            {/if}
            {$institutionselector|safe}
            {$progressbarform|safe}
            </div>
{include file="footer.tpl"}

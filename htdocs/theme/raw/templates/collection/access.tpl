{auto_escape off}
{include file="header.tpl"}
        <div class="message">{$accessdesc}<br /><br />
{if !$viewcount}
            <div>{str tag=noviews section=collection}</div>
{else}
            {if $master}
                <label>{str tag=currentmaster section=collection}: </label><a href="{$WWWROOT}view/access.php?id={$masterid}">{$master|safe}</a><br />
            {/if}
            <fieldset>
            <legend>{str tag=overrideaccess section=collection}</legend>
            {$form|safe}
            </fieldset>
{/if}
        </div>
{include file="footer.tpl"}
{/auto_escape}

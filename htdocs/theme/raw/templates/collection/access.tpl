{auto_escape on}
{include file="header.tpl"}
        <div class="message">
{if !$form}
            <div>{str tag=noviews section=collection}</div>
{else}
            {if $master}
                <label>{str tag=currentmaster section=collection}: </label><a href="{$WWWROOT}view/access.php?id={$master->view|safe}">{$master->title|safe}</a><br />
            {/if}
            <fieldset>
            <legend>{str tag=overrideaccess section=collection}</legend>
                {$form|safe}
            </fieldset>
{/if}
        </div>
{if $newform}
    {$newform|safe}
{/if}
{include file="footer.tpl"}
{auto_escape off}

{auto_escape off}
{if !$nosearch && $LOGGEDIN}        {user_search_form}{/if}
{if !$nosearch && !$LOGGEDIN && (count($LANGUAGES) > 1)}
        <form id="language-select" method="post" action="">
            <div>
                <label>{str tag=language}: </label>
                <select name="lang">
                    <option value="default" selected="selected">{$sitedefaultlang}</option>
{foreach from=$LANGUAGES key=k item=i}
                    <option value="{$k|escape}">{$i|escape}</option>
{/foreach}
                </select>
                <input type="submit" class="submit" name="changelang" value="{str tag=change}">
            </div>
        </form>
{/if}
{/auto_escape}

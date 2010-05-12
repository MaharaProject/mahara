{auto_escape off}
{include file="header.tpl"}

        <p>{str tag='viewlayoutpagedescription' section='view'}</p>

        {$form_start_tag}

            {foreach from=$options key=id item=description}
            <div class="fl">
                <div><img src="{$WWWROOT}thumb.php?type=viewlayout&amp;vl={$id}" alt=""></div>
                {if $id == $currentlayout}
                <div><input type="radio" class="radio" name="layout" value="{$id}" checked="checked"></div>
                {else}
                <div><input type="radio" class="radio" name="layout" value="{$id}"></div>
                {/if}
                <div>{$description|escape}</div>
            </div>
            {/foreach}
            <div class="cb">
                <input type="hidden" name="pieform_viewlayout" value="">
                {if $back}
                <input type="submit" class="submit submitcancel" name="cancel_submit" value="{str tag='back'}">
                {/if}
                <input type="submit" class="submit" name="submit" value="{str tag='changeviewlayout' section='view'}">
            </div>
        </form>

{include file="footer.tpl"}
{/auto_escape}

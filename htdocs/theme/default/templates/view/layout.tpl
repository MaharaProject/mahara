{include file="header.tpl"}

{include file="columnfullstart.tpl"}

        <h2>{str tag='viewlayout' section='view'}</h2>

        <p>{str tag='selectnumberofcolumns' section='view'}</p>

        {$columnsform}

{if $options}
        <p>{str tag='viewlayoutdescription' section='view'}</p>

        {$form_start_tag}

            {foreach from=$options key=id item=description}
            <div class="fl" style="margin: 0 .5em; text-align: center;">
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
                <input type="submit" class="submit" name="submit" value="{str tag='changeviewlayout' section='view'}">
            </div>
        </form>
{else}
        <p>{$nolayouts}</p>
{/if}

        <p><a href="{$WWWROOT}view/blocks.php?id={$view}&amp;new={$new}">&laquo; {str tag='backtoyourview' section='view'}</a></p>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}

{include file="header.tpl"}

{include file="columnfullstart.tpl"}

        <h2>Column Layout</h2>

        <p>You can change the widths of the columns in your view.</p>

        {$form_start_tag}

        {foreach from=$options key=id item=description}
        <div class="fl" style="margin: 0 .5em; text-align: center;">
            <div><img src="/thumb.php?type=blocktype&bt=test" alt=""></div>
            <div><input type="radio" class="radio" name="layout" value="{$id}"></div>
            <div>{$description|escape}</div>
        </div>
        {/foreach}
        <div style="clear: both;">
        <input type="hidden" name="pieform_viewlayout" value="">
        <input type="submit" class="submit" name="submit" value="Change Layout">
        </div>
        </form>

        <a href="blocks.php?id={$view}">Back to your view</a>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}

{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
  <h1>{$viewtitle}</h1>
{/if}

{include file="view/editviewtabs.tpl" selected='layout'}
<div class="subpage rel">

        <p>{str tag='viewlayoutpagedescription' section='view'}</p>

        {$form_start_tag|safe}

            {foreach from=$options key=id item=description}
            <div class="fl">
                <div><img src="{$WWWROOT}thumb.php?type=viewlayout&amp;vl={$id}" alt=""></div>
                {if $id == $currentlayout}
                <div><input type="radio" class="radio" name="layout" value="{$id}" checked="checked"></div>
                {else}
                <div><input type="radio" class="radio" name="layout" value="{$id}"></div>
                {/if}
                <div>{$description}</div>
            </div>
            {/foreach}
            <div class="cb">
                <input type="hidden" name="pieform_viewlayout" value="">
                {if $back}
                <input type="submit" class="cancel" name="cancel_submit" value="{str tag='back'}">
                {/if}
                <input type="submit" class="submit" name="submit" value="{str tag='changeviewlayout' section='view'}">
            </div>
        </form>

</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}

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

            {assign var=columns value=0}
            {foreach from=$options key=id item=description}
            {if $columns != $layouts[$id]->columns}
              {assign var=columns value=$layouts[$id]->columns}
              <div class="cb"></div>
              <div class="fl">{$layouts[$id]->columns} {if $columns > 1}{str tag=columns section=view}{else}{str tag=column section=view}{/if}</div>
            {/if}
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
                <input type="submit" class="submit" name="submit" value="{str tag='save'}">
            </div>
        </form>

</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}

{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
  <h1>{$viewtitle}</h1>
{/if}

{include file="view/editviewtabs.tpl" selected='layout' new=$new}
<div class="subpage rel">

        <p>{str tag='viewlayoutpagedescription' section='view'}</p>

        {$form_start_tag|safe}

            {assign var=columns value=0}
            {foreach from=$options key=id item=description}
            {if $columns != $layouts[$id]->columns}
              {assign var=columns value=$layouts[$id]->columns}
              <hr class="cb" />
              <div class="fl"><strong>{$layouts[$id]->columns} {if $columns > 1}{str tag=columns section=view}{else}{str tag=column section=view}{/if}</strong></div>
            {/if}
            <div class="fl">
                {if $id == $currentlayout}
                <div><input type="radio" class="radio" name="layout" value="{$id}" checked="checked"></div>
                {else}
                <div><input type="radio" class="radio" name="layout" value="{$id}"></div>
                {/if}
                <div><img src="{$WWWROOT}thumb.php?type=viewlayout&amp;vl={$id}" alt=""></div>
                <div>{$description}</div>
            </div>
            {/foreach}
            <div class="cb">
                <input type="hidden" name="pieform_viewlayout" value="">
                <input type="submit" class="submit" name="submit" value="{str tag='save'}">
                <input type="hidden" name="sesskey" value="{$USER->get('sesskey')}">
            </div>
        </form>

</div>

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}

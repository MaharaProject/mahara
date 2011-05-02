{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
  <h1>{$viewtitle}</h1>
{/if}

{include file="view/editviewtabs.tpl" selected='content' new=$new}
<div class="subpage rel">

{if $columns}
    {if $togglepublic}<div class="fr">{$togglepublic|safe}</div>{/if}
    {str tag="editblockspagedescription" section="view"}
    <form action="{$formurl}" method="post">
        <input type="submit" name="{$action_name}" id="action-dummy" class="hidden">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="change" value="1">
        <input type="hidden" id="category" name="c" value="{$category}">
        <input type="hidden" name="sesskey" value="{$SESSKEY}">
        {if $new}<input type="hidden" name="new" value="1">{/if}
        <div id="page">
            <div id="top-pane">
                <div id="category-list">
                    {$category_list|safe}
                </div>
                <div id="blocktype-list">
                    {$blocktype_list|safe}
                </div>
                <div class="cb"></div>
            </div>

{if $viewthemes}
            <div id="middle-pane" class="center">
                    <label for="viewtheme-select">{str tag=theme}: </label>
                    <select id="viewtheme-select" name="viewtheme">
{foreach from=$viewthemes key=themeid item=themename}
                        <option value="{$themeid}"{if $themeid == $viewtheme} selected="selected" style="font-weight: bold;"{/if}>{$themename}</option>
{/foreach}
                    </select>
            </div>
{/if}

            <div id="bottom-pane">
                <div id="column-container">
                	<div id="blocksinstruction" class="center">
                	    {str tag='blocksintructionnoajax' section='view'}
                	</div>
                        {$columns|safe}
                    <div class="cb"></div>
                </div>
            </div>
            <script type="text/javascript">
            {literal}
            insertSiblingNodesAfter('bottom-pane', DIV({'id': 'views-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));
            {/literal}
            </script>
        </div>
    </form>

    <div id="view-wizard-controls" class="center">
        <form action="{$WWWROOT}{if $groupid}{if $viewtype == 'grouphomepage'}group/view.php{else}view/groupviews.php{/if}{elseif $institution}view/institutionviews.php{else}view{/if}" method="GET">
        {if $groupid}
            {if $viewtype == 'grouphomepage'}
            <input type="hidden" name="id" value="{$groupid}">
            {else}
            <input type="hidden" name="group" value="{$groupid}">
            {/if}
        {elseif $institution}
            <input type="hidden" name="institution" value="{$institution}">
        {/if}
            <input class="submit" type="submit" value="{str tag='done'}">
        </form>
    </div>

{elseif $block}
    <div class="blockconfig-background">
        <div class="blockconfig-container">
            {$block.html|safe}
        </div>
    </div>
    {if $block.javascript}<script type="text/javascript">{$block.javascript|safe}</script>{/if}
{/if}
</div>
{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}

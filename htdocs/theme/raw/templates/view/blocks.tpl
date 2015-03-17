{if $microheaders}
  {include file="viewmicroheader.tpl"}
{else}
  {include file="header.tpl"}
{/if}

{include file="view/editviewtabs.tpl" selected='content' new=$new issiteview=$issiteview}
<div class="subpage rel cl" id="tabcontent" selected='content'>

  <form action="{$formurl}" method="post">
  <input type="submit" name="{$action_name}" id="action-dummy" class="hidden">
  <input type="hidden" id="viewid" name="id" value="{$view}">
  <input type="hidden" name="change" value="1">
  <input type="hidden" id="category" name="c" value="{$category}">
  <input type="hidden" name="sesskey" value="{$SESSKEY}">
  {if $new}<input type="hidden" name="new" value="1">{/if}
  <div id="editcontent-sidebar-wrapper">
    <div id="editcontent-sidebar">
    {include file="view/contenteditor.tpl" selected='content' new=$new}
    {if $viewthemes}
        <div id="select-theme">
            <div id="select-theme-header">{str tag=theme section=view}</div>
            <select id="viewtheme-select" name="viewtheme">
            {foreach from=$viewthemes key=themeid item=themename}
                <option value="{$themeid}"{if $themeid == $viewtheme} selected="selected" style="font-weight: bold;"{/if}>{$themename}</option>
            {/foreach}
            </select>
        </div>
    {/if}
    </div>
  </div>

{if $columns}
        <div id="page">
            <div id="bottom-pane">
                <div id="column-container">
                    <div id="blocksinstruction" class="center">
                        {str tag='blocksintructionnoajax' section='view'}
                    </div>
                        {$columns|safe}
                    <div class="cb"></div>
                </div>
            </div>
            <script type="application/javascript">
            {literal}
            insertSiblingNodesAfter('bottom-pane', DIV({'id': 'views-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));
            {/literal}
            </script>
        </div>
    </form>

    <div id="view-wizard-controls" class="center">
        {if $issitetemplate}
            <form action="{$WWWROOT}admin/site/views.php" method="GET">
                <input class="submit" type="submit" value="{str tag='done'}">
            </form>
        {else}
            <form action="{$WWWROOT}{if $groupid}{if $viewtype == 'grouphomepage'}{$groupurl}{else}view/groupviews.php{/if}
                    {elseif $institution}view/institutionviews.php{elseif $viewtype == 'dashboard'}index.php{else}view/index.php{/if}" method="GET">
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
        {/if}
    </div>

{elseif $block}
    <div class="blockconfig-background">
        <div class="blockconfig-container">
            {$block.html|safe}
        </div>
    </div>
    {if $block.javascript}<script type="application/javascript">{$block.javascript|safe}</script>{/if}
{/if}
</div>
<div id="addblock" class="blockinstance cb configure vertcentre hidden" role="dialog" aria-labelledby="addblock-heading" tabindex="-1">
    <div class="blockinstance-controls">
        <input type="image" src="{theme_image_url filename=btn_close}" class="deletebutton" name="action_removeblockinstance_id_{$id}" alt="{str tag=Close}">
    </div>
    <div class="blockinstance-header">
        <h2 id="addblock-heading" class="title"></h2>
    </div>
    <div class="blockinstance-content">
        {$addform|safe}
    </div>
</div>
<div id="configureblock" class="blockinstance cb configure vertcentre hidden" role="dialog">
    <div class="blockinstance-controls">
        <input type="image" src="{theme_image_url filename=btn_close}" class="deletebutton" name="close_configuration" alt="{str tag=closeconfiguration section=view}">
    </div>
    <div class="blockinstance-header">
    </div>
    <div class="blockinstance-content">
    </div>
</div>
{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}

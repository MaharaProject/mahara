{if $microheaders}{include file="viewmicroheader.tpl"}{else}{include file="header.tpl"}{/if}
<h1>{$maintitle}</h1>

{if $columns}
    {str tag="editblockspagedescription" section="view"}

    <form action="{$formurl}" method="post">
        <input type="submit" name="{$action_name}" id="action-dummy" class="hidden">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="change" value="1">
        <input type="hidden" id="category" name="c" value="{$category}">
        {if $new}<input type="hidden" name="new" value="1">{/if}
        <div id="page">
            <div id="top-pane">
                <div id="category-list">
                    {$category_list}
                </div>
                <div id="blocktype-list">
                    {$blocktype_list}
                </div>
                <div class="cb"></div>
            </div>

            <div id="middle-pane">
                <table class="fullwidth"><tr>
                    <td>
                        <a id="layout-link" href="columns.php?id={$view}&amp;c={$category}&amp;new={$new}"{if !$can_change_layout} class="disabled"{/if}>{str tag='changeviewlayout' section='view'}</a> {contextualhelp plugintype="core" pluginname="view" section="changeviewlayout"}
                    </td>
                    <td class="center">
                        <label for="viewtheme-select">{str tag=theme}: </label>
                        <select id="viewtheme-select" name="viewtheme">
                            <option value="">Choose theme...</option>
{foreach from=$viewthemes key=themeid item=themename}
                            <option value="{$themeid|escape}"{if $themeid == $viewtheme} selected="selected" style="font-weight: bold;"{/if}>{$themename|escape}</option>
{/foreach}
                        </select>
                    </td>
                    <td class="right">
                        <a id="btn-displaymyview" href="view.php?id={$view}&amp;new={$new}">{str tag=displaymyview section=view} &raquo;</a></td>
                    </td>
                </tr></table>
            </div>

            <div id="bottom-pane">
                <div id="column-container">
                	<div id="blocksinstruction" class="center">
                	    {str tag='blocksintructionnoajax' section='view'}
                	</div>
                	    {$columns}
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
    {if $new}
        <form action="" method="POST">
            <input type="hidden" name="id" value="{$view}">
            <input type="hidden" name="new" value="1">
            <input type="submit" name="cancel" class="submit" value="{str tag='cancel'}" onclick="return confirm('{str tag='confirmcancelcreatingview' section='view'}');">
        </form>
        <form action="{$WWWROOT}view/edit.php" method="GET">
            <input type="hidden" name="id" value="{$view}">
            <input type="hidden" name="new" value="1">
            <input type="submit" class="submit" value="{str tag=next}: {str tag='edittitleanddescription' section=view}">
        </form>
    {elseif $profile}
        <form action="{$WWWROOT}artefact/internal/index.php" method="GET">
            <input class="submit" type="submit" value="{str tag='done'}">
        </form>
    {else}
        <form action="{$WWWROOT}view/{if $groupid}groupviews.php{elseif $institution}institutionviews.php{/if}" method="GET">
        {if $groupid}
            <input type="hidden" name="group" value="{$groupid}">
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
            {$block.html}
        </div>
    </div>
{/if}

{if $microheaders}{include file="microfooter.tpl"}{else}{include file="footer.tpl"}{/if}

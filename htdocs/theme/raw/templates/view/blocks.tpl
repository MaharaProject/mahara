{include file="header.tpl"}

{include file="view/editviewtabs.tpl" selected='content' new=$new issiteview=$issiteview}
<div id="blocksinstruction" class="lead view-description">
        {str tag='blocksintructionnoajax' section='view'}
</div>

<div class="row view-container" selected='content' data-target="col-collapse">

    {if $columns}
        <form action="{$formurl}" method="post">
            <input type="submit" name="{$action_name}" id="action-dummy" class="hidden">
            <input type="hidden" id="viewid" name="id" value="{$view}">
            <input type="hidden" name="change" value="1">
            <input type="hidden" id="category" name="c" value="{$category}">
            <input type="hidden" name="sesskey" value="{$SESSKEY}">
            {if $new}<input type="hidden" name="new" value="1">{/if}

            <div id="editcontent-sidebar-wrapper" class="col-collapse">
                <div id="editcontent-sidebar" data-spy="affix" data-offset-top="420" data-offset-top="100" class="toolbar-affix">
                {include file="view/contenteditor.tpl" selected='content' new=$new}
                {if $viewthemes}
                    <div id="select-theme" class="select dropdown theme-dropdown">
                        <label id="select-theme-header">{str tag=theme section=view}</label>
                        <span class="picker">
                        <select id="viewtheme-select" class="form-control select" name="viewtheme">
                        {foreach from=$viewthemes key=themeid item=themename}
                            <option value="{$themeid}"{if $themeid == $viewtheme} selected="selected"{/if}>{$themename}</option>
                        {/foreach}
                        </select>
                        </span>
                    </div>
                {/if}
                </div>
            </div>
            <div class="col-with-collapse">
                <div id="bottom-pane" data-role="workspace">
                    <div id="column-container" class="user-page-content">
                        {$columns|safe}
                    </div>
                </div>
            </div>
        </form>

        <div id="view-wizard-controls" class="col-collapse-offset col-with-collapse">

            {if !$issitetemplate}
            <a class="btn btn-default" href="{$displaylink}">
                {str tag=displayview section=view}
                <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
            </a>
            {/if}
            {if $groupid}
            <a class="btn btn-default" href="{$WWWROOT}view/groupviews.php?group={$groupid}">
                {str tag=returntogrouppages section=group}
                <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
            </a>
            {/if}
        </div>

    {elseif $block}
        <form action="{$formurl}" method="post">
            <input type="submit" name="{$action_name}" id="action-dummy" class="hidden">
            <input type="hidden" id="viewid" name="id" value="{$view}">
            <input type="hidden" name="change" value="1">
            <input type="hidden" id="category" name="c" value="{$category}">
            <input type="hidden" name="sesskey" value="{$SESSKEY}">
            {if $new}<input type="hidden" name="new" value="1">{/if}

            <div id="editcontent-sidebar-wrapper" class="col-collapse">
                <div id="editcontent-sidebar">
                    {include file="view/contenteditor.tpl" selected='content' new=$new}
                    {if $viewthemes}
                    <div id="select-theme" class="select dropdown theme-dropdown">
                        <label id="select-theme-header">{str tag=theme section=view}</label>
                        <span class="picker">
                        <select id="viewtheme-select" class="form-control select" name="viewtheme">
                        {foreach from=$viewthemes key=themeid item=themename}
                            <option value="{$themeid}"{if $themeid == $viewtheme} selected="selected"{/if}>{$themename}</option>
                        {/foreach}
                        </select>
                        </span>
                    </div>
                    {/if}
                </div>
            </div>
            <div class="blockconfig-background">
                <div class="blockconfig-container">
                        {$block.html|safe}
                </div>
            </div>
            {if $block.javascript}
            <script type="application/javascript">
                    {$block.javascript|safe}
            </script>
            {/if}
        </form>
    {/if}

</div>

<div class="modal" id="addblock" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" data-height=".modal-body">
            <div class="modal-header">
                <button class="deletebutton close" name="action_removeblockinstance_id_{$id}">
                    <span class="times">&times;</span>
                    <span class="sr-only">{str tag=Close}</span>
                </button>
                <h4 class="modal-title blockinstance-header text-inline" id="addblock-heading"></h4>
            </div>
            <div class="modal-body blockinstance-content">
                <div class="block-inner">{$addform|safe}</div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-shown modal-docked-right modal-docked closed blockinstance configure" id="configureblock" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" data-height=".modal-body">
            <div class="modal-header">
                <button class="deletebutton close" name="close_configuration">
                    <span class="times">&times;</span>
                    <span class="sr-only">{str tag=closeconfiguration section=view}</span>
                </button>
                <h4 class="modal-title blockinstance-header text-inline"></h4>
                <span class="icon icon-cogs icon-2x pull-right" role="presentation" aria-hidden="true"></span>
            </div>
            <div class="modal-body blockinstance-content">
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}

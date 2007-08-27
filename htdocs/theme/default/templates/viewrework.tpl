{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <form action="" method="post">
        <div id="page">
            <div id="top-pane">
                <div id="category-list">
                    {$category_list}
                </div>
                <div id="blocktype-list">
                    {$blocktype_list}
                </div>
                <div id="blocktype-footer"></div>
            </div>

            <div id="bottom-pane">
                <div id="column-container">
{foreach from=$columns.columns key=colnum item=column}
                    <div class="column columns{$columns.count}">
                        <div class="column-header">
{if $colnum == 1}
                            <div class="add-column-left">
                                <input type="submit" class="submit" name="add_column_before_1" value="Add Column">
                            </div>
{/if}
                            <div class="remove-column">
                                <input type="submit" class="submit" name="remove_column_{$colnum}" value="Remove Column">
                            </div>
{if $colnum == $columns.count}
                            <div class="add-column-right">
                                <input type="submit" class="submit" name="add_column_before_{$colnum+1}" value="Add Column">
                            </div>
{else}
                            <div class="add-column-center">
                                <input type="submit" class="submit" name="add_column_before_{$colnum+1}" value="Add Column">
                            </div>
{/if}
                        </div>
                        <div class="column-content">
                            <input type="submit" class="submit newblockhere" name="blocktype_add_top_{$colnum}" value="Add new block here">
{foreach from=$column.blockinstances item=blockinstance}
                            <div class="blockinstance" id="blockinstance_{$blockinstance.id}">
                                <div class="blockinstance-header">
                                    <h4>{$blockinstance.title|escape}</h4>
                                </div>
                                <div class="blockinstance-controls">
                                    {if $blockinstance.canmoveleft}<input type="submit" class="submit movebutton" name="blockinstance_{$blockinstance.id}_moveleft" value="&larr;">{/if}
                                    {if $blockinstance.canmovedown}<input type="submit" class="submit movebutton" name="blockinstance_{$blockinstance.id}_movedown" value="&darr;">{/if}
                                    {if $blockinstance.canmoveup}<input type="submit" class="submit movebutton" name="blockinstance_{$blockinstance.id}_moveup" value="&uarr;">{/if}
                                    {if $blockinstance.canmoveright}<input type="submit" class="submit movebutton" name="blockinstance_{$blockinstance.id}_moveright" value="&rarr;">{/if}
                                    <input type="submit" class="submit deletebutton" name="blockinstance_{$blockinstance.id}_delete" value="X">
                                </div>
                                <div class="blockinstance-content">
                                    {$blockinstance.content}
                                </div>
                            </div>
                            <input type="submit" class="submit newblockhere" name="blocktype_add_after_{$blockinstance.id}" value="Add new block here">
{/foreach}
                        </div>
                    </div>
{/foreach}
                    <div id="clearer">
                        This is footer content
                    </div>
                </div>
            </div>
        </div>
    </form>









{include file="columnfullend.tpl"}

{include file="footer.tpl"}

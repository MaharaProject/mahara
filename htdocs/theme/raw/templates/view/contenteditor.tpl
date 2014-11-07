<div id="content-editor">
    <div id="content-editor-header"><div>{str tag=addcontent section=view}</div></div>
    <div id="content-editor-foldable">
        {if $shortcut_list}<div id="blocktype-common" class="blocktype-list">
            {* Blocks with the category 'shortcut' should always be showing. *}
            {$shortcut_list|safe}
        </div>{/if}
        <div id="accordion">
            {$category_list|safe}
        </div>
    </div>
</div>

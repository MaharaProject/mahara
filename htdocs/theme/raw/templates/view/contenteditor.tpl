<div id="content-editor" data-role="content-toolbar" class="content-toolbar">
    <div id="content-editor-header" class="sr-only">
        <div>{str tag=addcontent section=view}</div>
    </div>
    <div id="content-editor-foldable" class="btn-group-vertical btn-accordion fullwidth">
        {if $shortcut_list}
            {* Blocks with the category 'shortcut' should always be showing. *}
            {$shortcut_list|safe}
        {/if}
        {$category_list|safe}
    </div>
</div>

<div id="content-editor">
    <div id="content-editor-header"><div>{str tag=addcontent section=view}</div></div>
    <div id="content-editor-foldable">
        <div id="blocktype-common" class="blocktype-list">
            {* If you are wanting to have some options always showing place the code here. *}
            <div class="blocktype">
                <input type="radio" class="blocktype-radio" name="blocktype" value="textbox">
                <img width="24" height="24" title="{str tag=description section=blocktype.internal/textbox}" alt="{str tag=description section=blocktype.internal/textbox}" src="{$WWWROOT}thumb.php?type=blocktype&bt=textbox&ap=internal"><span class="blocktypetitle">{str tag='textbox' section='view'}</span>
            </div>
            <div class="blocktype lastrow">
                <input type="radio" class="blocktype-radio" name="blocktype" value="image">
                <img width="24" height="24" title="{str tag=description section=blocktype.file/image}" alt="{str tag=description section=blocktype.file/image}" src="{$WWWROOT}thumb.php?type=blocktype&bt=image&ap=file"><span class="blocktypetitle">{str tag='image' section='view'}</span>
            </div>
        </div>
        <div id="accordion">
            {$category_list|safe}
        </div>
    </div>
</div>

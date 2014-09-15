<div id="content-editor">
    <div id="content-editor-header"><div>{str tag=addcontent section=view}</div></div>
    <div id="content-editor-foldable">
        <div id="blocktype-common" class="blocktype-list">
            {* If you are wanting to have some options always showing place the code here. *}
            <div class="blocktype">
                <a class="blocktypelink" href="#">
                    <input type="radio" class="blocktype-radio" id="blocktype-radio-text" name="blocktype" value="text">
                    <img width="24" height="24" title="{str tag=description section=blocktype.text}" alt="{str tag=description section=blocktype.text}" src="{$WWWROOT}thumb.php?type=blocktype&bt=text">
                    <label for="blocktype-radio-text" class="blocktypetitle">{str tag='title' section='blocktype.text'}</label>
                </a>
            </div>
            <div class="blocktype lastrow">
                <a class="blocktypelink" href="#">
                    <input type="radio" id="blocktype-radio-image" class="blocktype-radio" name="blocktype" value="image">
                    <img width="24" height="24" title="{str tag=description section=blocktype.file/image}" alt="{str tag=description section=blocktype.file/image}" src="{$WWWROOT}thumb.php?type=blocktype&bt=image&ap=file">
                    <label for="blocktype-radio-image" class="blocktypetitle">{str tag='image' section='view'}</label>
                </a>
            </div>
        </div>
        <div id="accordion">
            {$category_list|safe}
        </div>
    </div>
</div>

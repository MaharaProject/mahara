{include file="header.tpl"}

<h2>CreateViewStep3</h2>

<table border="1">
    <tr>
        <td><div id="tree">Artefact Tree</div></td>
        <td>Template
<form action="" method="post">
    <input type="submit" name="cancel" value="{str tag=cancel}">
    <input type="submit" name="back" value="{str tag=back}">
    <input type="submit" name="submit" value="{str tag=next}">
</form>
        </td>
    </tr>
</table>
        
<script type="text/javascript">
{$rootinfo}

{literal}
function treeItemFormat(data, tree) {
    var item = LI({'id': data.id});
    if (data.container) {
        var toggleLink = SPAN({'id': data.id + '_toggle'}, tree.getExpandLink(item));
        appendChildNodes(item, toggleLink, ' ');
    }
    if (!data.title) {
        data.title = '';
    }
    var title = SPAN({title: data.title}, data.text);
    appendChildNodes(item, title);
    forEach(tree.statevars, function(j) {
        if (typeof(data[j]) != 'undefined') {
            item.setAttribute(j, data[j]);
        }
    });
    return item;
}

// API
var tree = new CollapsableTree(data, '/json/artefacttree.json.php');
tree.setToggleIcons('{/literal}{$plusicon}{literal}', '{/literal}{$minusicon}{literal}');
tree.setFormatCallback(treeItemFormat);
tree.statevars.push('pluginname');
tree.statevars.push('parent');
addLoadEvent(function () {
    swapDOM('tree', tree.render());
});
{/literal}
</script>

{include file="footer.tpl"}

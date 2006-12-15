{include file="header.tpl"}

<div id="column-full">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
	
{if $EDITMODE}
            <h2>{str tag=editview section=view}</h2>
{else}
            <h2>{str tag=createviewstep3 section=view}</h2>
{/if}

{literal}
<style type="text/css">
</style>
{/literal}
<div id="tree">Artefact Tree</div>
<div id="template">
    <form action="" method="post">
    {$template}
{if $EDITMODE}
        <input type="hidden" name="viewid" value="{$viewid}">
{/if}
        <input type="submit" name="cancel" value="{str tag=cancel}">
{if $EDITMODE}
        <input type="submit" name="submit" value="{str tag=save}">
{else}
        <input type="submit" name="back" value="{str tag=back section=view}">
        <input type="submit" name="submit" value="{str tag=next section=view}">
{/if}
    </form>
</div>
<script type="text/javascript">
{$rootinfo}

{literal}
function treeItemFormat(data, tree) {
    var item = LI({'id': 'art_' + data.id});

    if (data.container) {
        var toggleLink = SPAN({'id': 'art_' + data.id + '_toggle'}, tree.getExpandLink(item));
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


    if (data.isartefact) {
        title.artefactid        = data.id;
        title.artefacttype      = data.type;
        title.artefactrendersto = data.rendersto;

        new MoveSource(title, {
            'selectedClass': 'moveselected',
            'acceptData': {
                'type': data.type,
                'rendersto': data.rendersto,
                'plugin': data.pluginname
            }
        });
    }

    return item;
}

// API
var tree = new CollapsableTree(data, '{/literal}{$WWWROOT}{literal}/json/artefacttree.json.php');
tree.setToggleIcons('{/literal}{$plusicon}{literal}', '{/literal}{$minusicon}{literal}');
tree.setFormatCallback(treeItemFormat);
tree.statevars.push('pluginname');
tree.statevars.push('parent');
addLoadEvent(function () {
    appendChildNodes('tree', tree.render());
    expandDownToViewport('tree');
    expandDownToViewport('template');
});

{/literal}
</script>

	</div>
</span></span></span></span></div>	
</div>
</div>
{include file="footer.tpl"}

{include file="header.tpl"}

<div class="btn-group btn-group-top only-button">
    <a class="btn btn-default btn-group-item pull-left" href="{$wwwroot}module/framework/frameworks.php?upload=1">
        <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
        <span class="btn-title">{str tag="addframework" section="module.framework"}</span>
    </a>
</div>

<h4>{str tag="frameworks" section="module.framework"}</h4>
<p class="lead">{str tag="frameworksdesc" section="module.framework"}</p>

<table class="listing table table-striped text-small">
<thead>
    <tr>
        <th>{str tag="name"}</th>
        <th>{str tag="usedincollections" section="module.framework"}</th>
        <th>{str tag="selfassess" section="module.framework"}</th>
        <th>{str tag="active"}</th>
    </tr>
</thead>
<tbody>
{foreach from=$frameworks key=k item=item}
    <tr>
        <td>{$item->name}</td>
        <td>{$item->collections}</td>
        <td>{$item->selfassess}</td>
        <td class="buttonscell framework">{$item->activationswitch|safe}
        <script type="application/javascript">
            jQuery('#framework{$item->id}_enabled').on('change', function() {
                // save switch
                jQuery.post(config.wwwroot + 'module/framework/frameworks.json.php', jQuery('#framework{$item->id}').serialize())
                .done(function(data) {
                    console.log(data);
                });
            });
        </script>
        {$item->delete|safe}</td>
    </tr>
{/foreach}
</tbody>
</table>

{include file="footer.tpl"}

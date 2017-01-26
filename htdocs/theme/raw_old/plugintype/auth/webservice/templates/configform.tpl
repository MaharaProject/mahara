{include file="header.tpl"}
<p class="lead">{$pagedescription}</p>

<div class="collapsible-group" id="accordion" aria-multiselectable="true" role="tablist" >
{foreach from=$form.elements item=element name=elements}
    <div class="pseudofieldset panel panel-default collapsible collapsible-group{if $.foreach.elements.last} last{/if}">
    <h2 class="pseudolegend panel-heading has-link">
        <a class="{if !$.foreach.elements.first}collapsed{/if}" href="#{$element.name}_pseudofieldset" data-toggle="collapse" aria-expanded="{if $.foreach.elements.first}true{else}false{/if}" aria-controls="{$element.name}_pseudofieldset" data-parent="#accordion">
        {$element.legend}
         <span class="icon icon-chevron-down right collapse-indicator pull-right" role="presentation" aria-hidden="true"></span>
        </a>
    </h2>
    <div class="panel-body collapse {if $.foreach.elements.first} in{/if}" id="{$element.name}_pseudofieldset">
    {foreach from=$element.elements item=item}
        {$item.value|safe}
    {/foreach}
    </div>
    </div>
{/foreach}
</div>
<script type="application/javascript">
jQuery(function() {
    jQuery('.pseudofieldset').each(function(index) {
        jQuery(this).find('.pseudolegend').click(function(event) {
            jQuery(event.target).find('.panel-body').collapse('toggle');
        });
        // Keep open current section after save/reload of page.
        // Will reopen only the fieldset that had a form saved within it
        var opened = '{$opened}';
        if (jQuery(this).find('#' + opened + '_pseudofieldset').length) {
            // collapse the others
            jQuery('#accordion .panel-body.in').collapse('hide');
            jQuery(this).find('#' + opened + '_pseudofieldset').parent().find('.panel-body').collapse('show');
        }
    });
});

</script>
{include file="footer.tpl"}

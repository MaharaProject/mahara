{foreach from=$data key=sectionkey item=sectionvalue name=section}
<div class="form-group collapsible-group {if $.foreach.section.first}first{/if}  {if $.foreach.section.last}last{/if}">
    <fieldset class="pieform-fieldset collapsible {if $.foreach.section.first}first{/if} {if $.foreach.section.last}last{/if}">
        <legend>
            <button class="collapsed" type="button" aria-expanded="false" href="#{$sectionkey}" data-bs-toggle="collapse" data-bs-target="#{$sectionkey}">
                {str tag=$sectionkey section=statistics}
                <span class="icon icon-chevron-down right float-end collapse-indicator" role="presentation" aria-hidden="true"></span>
            </button>
        </legend>
        <div id="{$sectionkey}" class="fieldset-body collapse">
            <table class="table table-striped table-bordered" id="register-table">
            <thead>
                <tr>
                    <th>{str tag=Field section=admin}</th>
                    {if $sectionvalue.activecolumn}
                    <th class="cell-center">{str tag=active section=statistics}</th>
                    {/if}
                    <th{if $sectionvalue.activecolumn} class="text-end"{/if}>{str tag=Value section=admin}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $sectionvalue.data key=title item=value}
                <tr>
                    <th>{$value.key}</th>
                    {if $sectionvalue.activecolumn}
                    <td class="cell-center">{$value.active|safe}</td>
                    {/if}
                    <td{if $sectionvalue.activecolumn} class="text-end"{/if}>{$value.value}</td>
                </tr>
                {/foreach}
            </tbody>
            </table>
        </div>
    </fieldset>
</div>
{/foreach}

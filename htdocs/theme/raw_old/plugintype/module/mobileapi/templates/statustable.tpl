<div class="alert alert-default">
    {if $header}<h3>{$header}</h3>{/if}
    <p>{$notice|safe}</p>
</div>
<table class="table fullwidth table-padded">
    <tbody>
        <thead>
            <tr>
                <th>{str tag="configstep" section="module.mobileapi"}</th>
                <th>{str tag="configstepstatus" section="module.mobileapi"}</th>
            </tr>
        </thead>
        {foreach from=$statuslist item=item}
        <tr>
            <td>
                <h3 class="title">
                    {$item.name}
                </h3>
            </td>
            <td>
                {if $item.status}
                    <span class="icon icon-check text-success" title="{str tag="readylabel" section="module.mobileapi"}" role="presentation" aria-hidden="true"></span>
                {else}
                    <span class="icon icon-exclamation-triangle" title="{str tag="notreadylabel" section="module.mobileapi"}" role="presentation" aria-hidden="true"></span>
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
<script type="text/javascript">
if (typeof module_mobileapi_reload_page === "undefined") {
    function module_mobileapi_reload_page() {
        window.location.reload(true);
    }
}
</script>
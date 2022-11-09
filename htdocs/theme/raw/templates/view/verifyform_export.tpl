<div id="verifyform" class="toolbarhtml view-signoff">
    <div>
        <div class="signoff-title">
            {str tag=signedoff section=view}: {if $signoff}{str tag='switchbox.on' section='pieforms'}{else}{str tag='switchbox.off' section='pieforms'}{/if}
        </div>
    </div>
    {if $showverify}
    <div class="clearright">
        <div class="verified-title">
            {str tag=verified section=view}: {if $verified}{str tag='switchbox.on' section='pieforms'}{else}{str tag='switchbox.off' section='pieforms'}{/if}
        </div>
    </div>
    {/if}
</div>
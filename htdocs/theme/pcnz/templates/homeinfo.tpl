<div id="home-info-container" class="dashboard-widget-container">

    <div class="home-info-boxes{if $USER->is_logged_in()} loggedin{/if} fullwidth">

        {if $USER->is_logged_in()}
        <a href="{$url.views}" title="{str tag=create}" class="logged-in thumbnail-widget first">
        {else}
        <div class="logged-out thumbnail-widget first">
        {/if}
            <div id="home-info-create" class="widget home-info-box">
                <div class="widget-heading">
                    <div class="circle-bg">
                        <span class="icon icon-plus" role="presentation" aria-hidden="true"></span>
                    </div>
                    <div class="widget-content">
                        <h2 class="title">
                            {str tag=create}
                        </h2>
                        <p>{str tag=createsubtitle}</p>
                    </div>
                </div>
            </div>
        {if $USER->is_logged_in()}
        </a>
        {else}
        </div>
        {/if}

        {if $USER->is_logged_in()}
        <a href="https://myrecert.pharmacycouncil.org.nz/view/view.php?id=17" target="_blank" class="logged-in thumbnail-widget">
        {else}
        <div class="logged-out thumbnail-widget">
        {/if}
            <div id="home-info-share" class="widget home-info-box">
                <div class="widget-heading">
                    <div class="circle-bg">
                        <span class="icon icon-question" role="presentation" aria-hidden="true"></span>
                    </div>
                    <div class="widget-content">
                        <h2 class="title">
                            {str tag=howto}
                        </h2>
                        <p>{str tag=howtosubtitle}</p>
                    </div>
                </div>
        </div>
        {if $USER->is_logged_in()}
        </a>
        {else}
        </div>
        {/if}
    </div>
</div>

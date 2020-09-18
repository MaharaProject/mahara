<div id="home-info-container" class="dashboard-widget-container">

    <div class="home-info-boxes{if $USER->is_logged_in()} loggedin{/if} fullwidth">

        {if $USER->is_logged_in()}
        <a href="{$url.views}" title="{str tag=create}" class="logged-in thumbnail-widget first">
        {else}
        <span class="logged-out thumbnail-widget first">
        {/if}
            <div id="home-info-create" class="widget home-info-box">
            <span class="puzzle">
            </span>
                <div class="widget-heading">
                    <h2 class="title">
                        {str tag=create}
                    </h2>
                    <div class="pict"><img src="{theme_url filename='images/dash-create.png'}" alt="Engage"></div>
                    <p>{str tag=createsubtitle}</p>
                </div>
            </div>
        {if $USER->is_logged_in()}
        </a>
        {else}
        </span>
        {/if}

        {if $USER->is_logged_in()}
        <a href="{$url.share}" class="logged-in thumbnail-widget">
        {else}
        <span class="logged-out thumbnail-widget">
        {/if}
            <div id="home-info-share" class="widget home-info-box">
            <span class="puzzle">
            </span>
                <div class="widget-heading">
                    <h2 class="title">
                        {str tag=share}
                    </h2>
                    <div class="pict"><img src="{theme_url filename='images/dash-share.png'}" alt="Engage"></div>
                    <p>{str tag=sharesubtitle}</p>
                </div>
            </div>
        {if $USER->is_logged_in()}
        </a>
        {else}
        </span>
        {/if}

        {if $USER->is_logged_in()}
        <a href="{$url.groups}" class="logged-in thumbnail-widget last">
        {else}
        <span class="logged-out thumbnail-widget last">
        {/if}
            <div id="home-info-engage" class="widget home-info-box">
            <span class="puzzle">
            </span>
                <div class="widget-heading">
                    <h2 class="title">
                        {str tag=engage}
                    </h2>
                    <div class="pict"><img src="{theme_url filename='images/dash-engage.png'}" alt="Engage"></div>
                    <p>{str tag=engagesubtitle}</p>
                </div>
            </div>
        {if $USER->is_logged_in()}
        </a>
        {else}
        </span>
        {/if}
        <span class="penciltip">
            <div class="puzzlewrap">
                <span class="puzzle">
                    <span class="pencillead"></span>
                </span>
            </div>
        </span>
    </div>
</div>

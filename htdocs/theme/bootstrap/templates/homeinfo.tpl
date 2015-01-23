<div id="home-info-container" class="dashboard-widget-container">
    {if $USER->is_logged_in()}
        <div id="hideinfo" class="nojs-hidden-block align-right">
            <a href="#" title="{str tag=Hide2}" class="remove-widgets">
                <span class="glyphicon glyphicon-remove-circle"></span>
                <span class="sr-only">{str tag=Close}</span>
            </a>
        </div>
    {/if}
    <div class="home-info-boxes{if $USER->is_logged_in()} loggedin{/if} fullwidth">
    <div class="row">
        <div class="col-sm-4">
            {if $USER->is_logged_in()}
            <a href="{$url.views}" title="{str tag=create}" class="logged-in">{/if}
                <div id="home-info-create" class="widget">
                    <div class="widget-heading">
                        <div class="circle-bg mrm">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </div>
                        <h2 class="title">
                            {str tag=create}
                        </h2>
                        <p>{str tag=createsubtitle}</p>
                    </div>
                    <div class="widget-detail">
                        <p>{str tag=createdetail}</p>
                    </div>
                </div>
            {if $USER->is_logged_in()}
            </a>{/if}
        </div>
        <div class="col-sm-4">
            {if $USER->is_logged_in()}
            <a href="{$url.share}" class="logged-in">{/if}
                <div id="home-info-share" class="home-info-box">
                    <div class="widget-heading">
                        <div class="circle-bg mrm">
                            <span class="glyphicon glyphicon-share"></span>
                        </div>
                        <h2 class="title">
                            {str tag=share}
                        </h2>
                        <p>{str tag=sharesubtitle}</p>
                    </div>
                    <div class="widget-detail">
                        <p>{str tag=sharedetail}</p>
                    </div>
            </div>{if $USER->is_logged_in()}</a>{/if}
        </div>
        <div class="col-sm-4">
            {if $USER->is_logged_in()}
            <a href="{$url.groups}" class="logged-in">{/if}
                <div id="home-info-engage" class="home-info-box">
                    <div class="widget-heading">
                        <div class="circle-bg mrm">
                            <span class="glyphicon glyphicon-comment"></span>
                        </div>
                        <h2 class="title">
                            {str tag=engage}
                        </h2>
                        <p>{str tag=engagesubtitle}</p>
                    </div>
                    <div class="widget-detail">
                        <p>{str tag=engagedetail}</p>
                    </div>
                </div>{if $USER->is_logged_in()}
            </a>{/if}
        </div>
    </div>
    </div>
</div>

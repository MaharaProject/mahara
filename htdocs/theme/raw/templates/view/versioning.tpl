{include file="header.tpl"}

<div id="view-wizard-controls" class="with-heading">
    <a href="{$viewurl}" id="display_page">
        {str tag=displayview section=view}
        <span class="icon icon-arrow-circle-right right" role="presentation" aria-hidden="true"></span>
    </a>
</div>

<div class="grouppageswrap view-container">
    {$timelineform|safe}
{if $views}
    <div class="jtline"><span class="icon icon-spinner icon-pulse"></span> {str tag=loadingtimelinecontent section=view arg1=$viewtitle}</div>
    <script type="application/javascript">
    $(function () {
        var jtLine = $('.jtline').jTLine({
            callType: 'ajax',
            url:'versioning.json.php',
            eventsMinDistance: 100,
            distanceMode: 'fixDistance', // predefinedDistance , fixDistance
            fixDistanceValue: 2,
            firstPointMargin: 1.5,
            params: {literal}{{/literal}
              'sesskey': config.sesskey,
              {if $fromdate} 'fromdate' : '{$fromdate}', {/if}
              {if $todate}'todate' : '{$todate}', {/if}
              'view' : '{$view}',
              {literal}}{/literal},
            map: {
                "dataRoot": "/",
                "title": "taskTitle",
                "subTitle": "taskSubTitle",
                "dateValue": "assignDate",
                "idValue": "assignID",
                "pointCnt": "taskShortDate",
                "bodyCnt": "taskDetails"
            },
            formatTitle: function (title, obj) { return '<h3>' + title + '</h3>'; },
            formatSubTitle: function (subTitle, obj) { return '<div class="metadata">' + subTitle + '</div>'; },
            formatBodyContent: function (bodyCnt, obj) { return bodyCnt;}
        });
    });
    </script>
{/if}
</div>

{include file="footer.tpl"}

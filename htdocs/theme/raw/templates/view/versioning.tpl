{include file="header.tpl"}

<div class="pageactions" id="toolbar-buttons">
    <div class="btn-group-vertical in-editor">
        <a class="btn btn-secondary editviews" href="{$viewurl}" title="{str tag=displayview section=view}">
            <span class="icon icon-tv" aria-hidden="true" role="presentation"></span>
            <span class="btn-title sr-only">{str tag=displayview section=view}</span>
        </a>
    </div>
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
            formatTitle: function (title, obj) { return '<h2>' + title + '</h2>'; },
            formatSubTitle: function (subTitle, obj) { return '<div class="metadata">' + subTitle + '</div>'; },
            formatBodyContent: function (bodyCnt, obj) {
              if (obj.gridlayout) {
                  var grid = $('<div id="grid_' + obj.assignID + '" class="grid-stack"></div>');
                  var options = {
                      verticalMargin: 5,
                      cellHeight: 10,
                      float: true,
                      ddPlugin: false,
                  };
                  grid.gridstack(options);
                  grid = grid.data('gridstack');
                  loadGrid(grid, bodyCnt);

                  var container = $('<div class="container-fluid"></div>').append(grid.container);
                  return container[0].outerHTML;
              }
              else {
                  return bodyCnt;
              }
            }
        });
    });
    </script>
{/if}
</div>

{include file="footer.tpl"}

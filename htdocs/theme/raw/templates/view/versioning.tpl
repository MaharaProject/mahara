{include file="header.tpl"}

    <div class="grouppageswrap view-container">
        {$timelineform|safe}
        <div class="jtline"></div>
    </div>

    <script>
$(document).ready(function () {
  var jtLine = $('.jtline').jTLine({
              callType: 'ajax',
              url:'versioning.json.php',
              eventsMinDistance: 200,
              distanceMode: 'fixDistance',
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
                  "pointCnt": "taskShortDate",
                  "bodyCnt": "taskDetails"
              },
              formatTitle: function (title, obj) { return '<h3>' + title + '</h3>';},
              formatSubTitle: function (subTitle, obj)  { return '<div class="metadata">' + subTitle + '</div>';},
              formatBodyContent: function (bodyCnt, obj) { return bodyCnt;},
              onPointClick: function (e) {
                  console.log(e);
              }
          });
});
</script>

{include file="footer.tpl"}

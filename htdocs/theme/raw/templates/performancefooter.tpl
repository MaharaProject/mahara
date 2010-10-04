        <div id="performance-info">
{if $perf_memory_total}
            <span id="memoryused">{str tag="memoryused" section="performance"}: {$perf_memory_total_display}</span> |
{/if}
{if $perf_realtime}
            <span id="timeused">{str tag="timeused" section="performance"}: {$perf_realtime|number_format:3} {str tag="seconds" section="performance"}</span> |
{/if}
{if $perf_includecount}
            <span id="included">{str tag="included" section="performance"}: {$perf_includecount}</span> |
{/if}
{if $perf_dbreads || $perf_dbwrites || $perf_dbcached}
            <span id="dbqueries">{str tag="dbqueries" section="performance"}: {$perf_dbreads} {str tag='reads' section='performance'}, {$perf_dbwrites} {str tag='writes' section='performance'}, {$perf_dbcached} {str tag='cached' section='performance'}</span> |
{/if}
{if $perf_ticks}
            <span id="posixtimes">{str tag="ticks" section="performance"}: {$perf_ticks} {str tag="user" section="performance"}: {$perf_utime}
                {str tag="sys" section="performance"}: {$perf_stime} {str tag="cuser" section="performance"}: {$perf_cutime}
                {str tag="csys" section="performance"}: {$perf_cstime}</span> |
{/if}
{if $perf_serverload}
            <span id="serverload">{str tag="serverload" section="performance"}: {$perf_serverload}</span>
{/if}
        </div>


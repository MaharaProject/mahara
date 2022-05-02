                        </div>
                    </div>
                </main>
            </div>
        </div>
        {if !$htmllite}
        <footer class="footer">
            <div class="footer-inner container">
                <div class="metadata fullwidth site-performace">Export generated for {$user|full_name|escape} on {$export_time|format_date}, from their portfolio at <a href="{$WWWROOT}">{$sitename}</a></div>
            </div>
        </footer>
        {/if}
    </body>
</html>

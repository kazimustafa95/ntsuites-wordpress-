<div id="nitropack-container" class="wrap">
    <div id="heading">
        <h2><?php esc_html_e( 'NitroPack.io', 'nitropack' ); ?></h2>
    </div>

    <form method="post" action="options.php" name="form">
        <?php settings_fields( NITROPACK_OPTION_GROUP ); ?>
        <?php do_settings_sections( NITROPACK_OPTION_GROUP ); ?>

        <ul class="nav nav-tabs nav-tab-wrapper">
            <li><a class="nav-tab active" href="#dashboard" data-toggle="tab"><?php esc_html_e( 'Dashboard', 'nitropack' ); ?></a></li>
            <li><a class="nav-tab" href="#help" data-toggle="tab"><?php esc_html_e( 'Help', 'nitropack' ); ?></a></li>
	        <li><a class="nav-tab" href="#diag" data-toggle="tab"><?php esc_html_e( 'Diagnostics', 'nitropack' ); ?></a></li>
        </ul>		
        <div class="tab-content" style="display:block">
            <div id="dashboard" class="tab-pane hidden">
                <?php require_once NITROPACK_PLUGIN_DIR . "view/dashboard.php"; ?>
            </div>
            <div id="help" class="tab-pane hidden">
                <?php require_once NITROPACK_PLUGIN_DIR . "view/help.php"; ?>
            </div>
            <div id="diag" class="tab-pane hidden">
                <?php require_once NITROPACK_PLUGIN_DIR . "view/diag.php"; ?>
            </div>
        </div>
    </form>
</div>
<?php if (NITROPACK_SUPPORT_BUBBLE_VISIBLE) { ?>
<div class="support-widget">
  <!-- support widget -->
  <script>
    window.intercomSettings = {
      api_base: "https://api-iam.intercom.io",
      app_id: "d5v9p9vg"
    };

    (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/d5v9p9vg';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
  </script>
  <!-- end support widget -->
</div>
<?php } ?>
<script>
(function($) {
    window.Notification = (_ => {
        var timeout;

        var display = (msg, type) => {
            clearTimeout(timeout);
            $('#nitropack-notification').remove();

            $('[name="form"]').prepend('<div id="nitropack-notification" class="notice notice-' + type + '" is-dismissible"><p>' + msg + '</p></div>');

            timeout = setTimeout(_ => {
                $('#nitropack-notification').remove();
            }, 10000);
            loadDismissibleNotices();
        }

        return {
            success: msg => {
                display(msg, 'success');
            },
            error: msg => {
                display(msg, 'error');
            },
            info: msg => {
                display(msg, 'info');
            },
            warning: msg => {
                display(msg, 'warning');
            }
        }
    })();

    const clearCacheHandler = clearCacheAction => {
        return function(success, error) {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: "nitropack_" + clearCacheAction + "_cache"
                },
                dataType: 'json',
                beforeSend: function() { 
                    Notification.info("Loading. Please wait...");
                    $('#optimizations-purge-cache').hide();
                    $('#np-purge-cache-loading').show();
                },
                success: function(data) {
                    $('#np-purge-cache-loading').hide();
                    if (data.type === 'success') {
                        $('#np-purge-cache-success').show();
                        Notification[data.type](data.message);
                        cacheEvent = new Event("cache." + clearCacheAction + ".success");
                    } else {
                        $('#np-purge-cache-error').show();
                        Notification[data.type](data.message);
                        cacheEvent = new Event("cache." + clearCacheAction + ".error");
                    }
                    window.dispatchEvent(cacheEvent);
                },
                error: function(data) {
                    $('#np-purge-cache-loading').hide();
                    $('#np-purge-cache-error').show();
                    Notification[data.type](data.message);
                    cacheEvent = new Event("cache." + clearCacheAction + ".error");
                    window.dispatchEvent(cacheEvent);
                },
                complete: function() {
                    setTimeout(function(){$('#np-purge-cache-success').hide();$('#np-purge-cache-error').hide();$('#optimizations-purge-cache').show();}, 3000);
                }
            });
        };
    }

    $(window).on("load", _ => {
        //Remove styles from jobcareer and jobhunt plugins since they break our layout. They should not be loaded on our options page anyway.
        $('link[href*="jobcareer"').remove();
        $('link[href*="jobhunt"').remove();

        $("#dashboard").addClass("show active");
        window.addEventListener('cache.invalidate.request', clearCacheHandler("invalidate"));
        window.addEventListener('cache.purge.request', clearCacheHandler("purge"));

        NitroPack.QuickSetup.setChangeHandler(async function(value, success, error) {
            success(value);
        });
    });

    $("#nitro-restore-connection-btn").on("click", function() {
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: "nitropack_reconfigure_webhooks"
            },
            dataType: 'json',
            beforeSend: function() {
                $("#nitro-restore-connection-btn").attr("disabled", true).html("<i class='fa fa-refresh fa-spin'></i>");
            },
            success: function(data) {
                if (!data.status || data.status != "success") {
                    if (data.message) {
                        alert("<?php esc_html_e( 'Error:', 'nitropack' ); ?> " + data.message);
                    } else {
                        alert("<?php esc_html_e( 'Error: We were unable to restore the connection. Please contact our support team to get this resolved.', 'nitropack' ); ?>");
                    }
                } else {
                    $("#nitro-restore-connection-btn").attr("disabled", true).html("<i class='fa fa-check'></i>");
                }
            },
            complete: function() {
                location.reload();
            }
        });
    });
})(jQuery);
</script>

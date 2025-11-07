<div class="acpt-container <?php echo ACPT_SKIN; ?> <?php echo (is_rtl() === true) ? "rtl" : ""; ?>">
    <div id="acpt-admin-app"></div>
</div>
<?php if(ACPT_SKIN === 'dark'): ?>
    <!-- ACPT DARK SKIN -->
    <style>
        #wpfooter, #wpcontent {
            background: #1d2327;
            color: #ddd;
        }

        #wpfooter a {
            color: #00a1ba;
        }

        #wpfooter a:hover,
        #wpfooter a:focus {
            color: #007e91;
        }

        ul#adminmenu a.wp-has-current-submenu:after  {
            border-right-color: #1d2327;
        }
    </style>
<?php endif; ?>
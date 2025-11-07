(function($) {
    $().on('facetwp-refresh', function() {
        if (! FWP.loaded) {
            setup_bricks();
        }
    });

    $().on('facetwp-loaded', function() {
        if (FWP.loaded) {
            bricksLazyLoad();
            bricksPhotoswipe();
        }
    });

    FWP.hooks.addFilter('facetwp/template_html', function(resp, params) {
        var $html = $(params.response.template);
        var $element = '#bricks-dynamic-data-inline-css';
        var $styles = $html.find($element);
        if ($styles.len() > 0) {
            $styles_element = $('body').find($element);
            $styles_element.html(($styles.html()));
        }
        return resp;
    }, 1 );

    function setup_bricks() {

        // Intercept pagination
        $().on('click', '.bricks-pagination a', function(e) {
            e.preventDefault();
            var matches = $(this).attr('href').match(/\/page\/(\d+)/);

            if (null !== matches) {
                FWP.paged = parseInt(matches[1]);
            } else {
                FWP.paged = 0;
            }
            FWP.soft_refresh = true;
            FWP.refresh();
        });

        $().on('click', '.facetwp-template .woocommerce-pagination a', function(e) {
            e.preventDefault();
            var matches = $(this).attr('href').match(/product-page=(\d+)/);
            if (null !== matches) {
                FWP.paged = parseInt(matches[1]);
                FWP.soft_refresh = true;
                FWP.refresh();
            }
        });

    }
})(fUtil);

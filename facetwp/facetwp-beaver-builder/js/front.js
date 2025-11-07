var FWPBB = FWPBB || {};

(function($) {

    // Prevent BB scroll
    FLBuilderLayout._scrollToElement = function(el, callback) {
        var config  = FLBuilderLayoutConfig.anchorLinkAnimations;

        if (el.length > 0) {

            // See _initAnchorLink() within bb-plugin/js/fl-builder-layout.js
            if ( el.hasClass( 'fl-scroll-link' ) || el.hasClass( 'fl-row' ) || el.hasClass( 'fl-col' ) || el.hasClass( 'fl-module' ) ) {
                var ot = el.offset().top,
                    dh = $(document).height(),
                    wh = $(window).height();

                var dest = (ot > dh - wh) ? (dh - wh) : (ot - config.offset);

                $('html, body').animate({ scrollTop: dest }, config.duration, config.easing, function() {
                    if ('undefined' != typeof callback) {
                        callback();
                    }
                });
            }
        }
    }

    // Grids
    FWPBB.init_grids = function() {
        $.each(FWPBB.modules, function(id, obj) {
            if ('grid' === obj.layout) {
                if ('post-grid' === obj.type) {
                    new FLBuilderPostGrid(obj);
                    $('.fl-node-' + id + ' .fl-post-grid').masonry('reloadItems');
                }
                else if ('pp-content-grid' === obj.type) {
                    new PPContentGrid(obj);
                }
                else if ('blog-posts' === obj.type) {
                    new UABBBlogPosts(obj);
                }
                else if ('uabb-woo-products' === obj.type) {
                    new UABBWooProducts(obj);
                }
            }
            else if ('gallery' == obj.layout) {
                new FLBuilderPostGrid(obj);

                $('.fl-post-gallery-img').each(function() {
                    $(this)[0].style.setProperty('max-width', '100%', 'important');
                });
            }
            else if ('columns' === obj.layout) {
                if ('post-grid' === obj.type) {
                    new FLBuilderPostGrid(obj);
                }
            }
        });
        clean_pager();
    }

    function clean_pager() {
        $('.facetwp-bb-module a.page-numbers').attr('href', '').each(function() {
            $(this).trigger('init');
        });
    }

    $(document).on('click init', '.facetwp-bb-module a.page-numbers', function(e) {
        e.preventDefault();

        var $link = $(this);
        var page = $link.text();

        if ($link.hasClass('prev')) { // previous
            page = FWP.settings.pager.page - 1;
        }

        if ($link.hasClass('next')) { // next
            page = FWP.settings.pager.page + 1;
        }

        if (e.type === 'click') {
            FWP.paged = page;
            FWP.soft_refresh = true;
            FWP.refresh();
        }
        else {
            FWP.facets['paged'] = page;
            $link.attr('href', '?' + FWP.buildQueryString());
        }
    });

    $(document).on('facetwp-loaded', function() {
        if (FWP.loaded || FWP.is_bfcache) {
            FWPBB.init_grids();
        }
    });

    $(document).on('facetwp-refresh', function() {
        if ($('.facetwp-template:first').hasClass('facetwp-bb-module')) {
            FWP.template = 'wp';
        }
    });
})(jQuery);
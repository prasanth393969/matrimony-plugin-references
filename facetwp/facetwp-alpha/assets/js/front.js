(function($) {
    $(function() {
        FWP.hooks.addAction('facetwp/refresh/alpha', function($this, facet_name) {
            FWP.facets[facet_name] = $this.find('.facetwp-alpha.selected').attr('data-id') || '';
        });
    });

    $().on('click', '.facetwp-alpha.available', function() {
        var $parent = $(this).closest('.facetwp-facet');
        var is_selected = $(this).hasClass('selected');
        var facet_name = $parent.attr('data-name');

        $parent.find('.facetwp-alpha').removeClass('selected');

        if (! is_selected) {
            $(this).addClass('selected');
        }

        if ('' !== $(this).attr('data-id')) {
            FWP.frozen_facets[facet_name] = 'soft';
        }
        FWP.refresh();
    });
})(fUtil);

(function($) {
    FWP.hooks.addAction('facetwp/refresh/color', function($this, facet_name) {
        var selected_values = [];
        $this.find('.facetwp-color.checked').each(function() {
            selected_values.push($(this).attr('data-value'));
        });
        FWP.facets[facet_name] = selected_values;
    });

    FWP.hooks.addFilter('facetwp/selections/color', function(output, params) {
        var choices = [];
        $.each(params.selected_values, function(val) {
            var $item = params.el.find('.facetwp-color[data-value="' + val + '"]');
            var $display_value = $item.attr('title').length > 0 ? $item.attr('title') : val;
            choices.push({
                value: val,
                label: $display_value
            });
        });
        return choices;
    });

    $().on('click', '.facetwp-facet .facetwp-color:not(.disabled)', function(e) {
        if (true === e.handled) {
            return false;
        }
        e.handled = true;
        $(this).toggleClass('checked');
        FWP.autoload();
    });

    $().on('click', '.facetwp-type-color .facetwp-toggle', function() {
        var $parent = $(this).closest('.facetwp-facet');
        $parent.find('.facetwp-toggle').toggleClass('facetwp-hidden');
        $parent.find('.facetwp-overflow').toggleClass('facetwp-hidden');
    });

    $().on('facetwp-loaded', function() {
        $('.facetwp-color').each(function() {
            var $this = $(this);
            var el = $this.nodes[0];
            el.style.backgroundColor = $this.attr('data-color');
            if (null !== $this.attr('data-img')) {
                el.style.backgroundImage = 'url("' + $this.attr('data-img') + '")';
                el.style.backgroundPosition = 'center';
                el.style.backgroundSize = 'cover';
            }
        });

        $('.facetwp-type-color').each(function() {
            var num = $(this).find('.facetwp-color.facetwp-overflow').len();
            var $el = $(this).find('.facetwp-toggle-wrap .facetwp-toggle').first();
            if ( undefined != $el.text() ) $el.text($el.text().replace('{num}', num));

            // auto-expand if a color within the overflow is checked
            if (0 < $(this).find('.facetwp-color.checked').len()) {
                $el.trigger('click');
            }
        });
    });
})(fUtil);

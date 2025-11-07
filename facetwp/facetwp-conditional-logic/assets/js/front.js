(function($) {

    var facetsInUse = function() {
        var in_use = false;

        $.each(FWP.facets, function(val, name) {
            if (0 < val.length && 'paged' !== name) {
                in_use = true;
                return false; // exit loop
            }
        });

        return in_use;
    }

    var evaluateCondition = function(cond) {
        var is_valid = false;
        var compare_field;
        var compare_value = cond.value;

        if ('facets-empty' == cond.object) {
            return false === facetsInUse();
        }
        else if ('facets-not-empty' == cond.object) {
            return true === facetsInUse();
        }
        else if ('uri' == cond.object) {
            compare_field = FWP_HTTP.uri;
            if ('*' === cond.value.slice(-1)) {
                var len = cond.value.length - 1; // minus the "*"
                var temp_val = cond.value.substr(0, len);
                if (temp_val === compare_field.substr(0, len)) {
                    compare_field = compare_value = temp_val;
                }
            }
        }
        else if ('total-rows' == cond.object) {
            if ('undefined' === typeof FWP.settings.pager) {
                return false;
            }
            compare_field = FWP.settings.pager.total_rows;
        }
        else if ('facet-' == cond.object.substr(0, 6)) {
            var facet_name = cond.object.substr(6);
            if ('undefined' === typeof FWP.facets[facet_name]) {
                return false;
            }
            compare_field = FWP.facets[facet_name];
        }
        else if ('template-' == cond.object.substr(0, 9)) {
            compare_field = FWP.template;
            compare_value = cond.object.substr(9);
        }

        // operators
        if ('is' == cond.compare) {
            if (isIntersect(compare_value, compare_field)) {
                is_valid = true;
            }
        }
        else if ('not' == cond.compare) {
            if (! isIntersect(compare_value, compare_field)) {
                is_valid = true;
            }
        }

        return is_valid;
    }

    var isIntersect = function(arr1, arr2) {

        arr1 = ("string" == typeof arr1 ) ? arr1.split(/\s*(?:,|$)\s*/) : arr1;
        arr2 = ("string" == typeof arr2 ) ? arr2.split(/\s*(?:,|$)\s*/) : arr2;

        // force arrays
        arr1 = [].concat(arr1);
        arr2 = [].concat(arr2);

        // exact match
        if (arr1.toString() === arr2.toString()) {
            return true;
        }

        var result = arr1.filter(function(n) {
            return arr2.indexOf(n) != -1;
        });

        return result.length > 0;
    }

    var buildAliases = function() {

        $.each(FWP.settings.num_choices, function(val, key) {
            if (0 === val) {
                $('.facetwp-facet-' + key).addClass('is-empty').removeClass('not-empty');
            }
            else {
                $('.facetwp-facet-' + key).addClass('not-empty').removeClass('is-empty');
            }
        });

        $.each(FWP.facets, function(val, key) {
            if (val && 0 < val.length) {
                $('.facetwp-facet-' + key).addClass('is-active').removeClass('not-active');
            }
            else {
                $('.facetwp-facet-' + key).addClass('not-active').removeClass('is-active');
            }
        });
    }

    var doAction = function(action, is_valid) {
        var item;
        var is_custom = false;
        var animation = ".addClass('is-hidden')"; // hide

        if ('template' == action.object) {
            item = '.facetwp-template';
        }
        else if ('facets' == action.object) {
            item = '.facetwp-facet';
        }
        else if ('facet-' == action.object.substr(0, 6)) {
            item = '.facetwp-facet-' + action.object.substr(6);
        }
        else if ('custom' == action.object) {
            is_custom = true;
            var lines = action.selector.split("\n");
            var selectors = [];
            for (var i = 0; i < lines.length; i++){
                var selector = lines[i].replace(/^\s+|\s+$/gm, '');
                if (selector.length) {
                    selectors.push(selector);
                }
            }
            item = selectors;
        }

        if (item.length < 1) {
            return;
        }

        if (('show' == action.toggle && is_valid) || ('hide' == action.toggle && ! is_valid)) {
            animation = ".removeClass('is-hidden')"; // show
        }

        // toggle
        if (is_custom) {
            $.each(item, function(selector) {
                var first_part = selector.split('.')[0];

                if (['$EMPTY', '$NONEMPTY', '$ACTIVE', '$INACTIVE'].includes(first_part)) {

                    if (!is_valid) {
                        if ('$EMPTY' == first_part) {
                            selector = selector.replace('$EMPTY', '$NONEMPTY');
                        }
                        else if ('$NONEMPTY' == first_part) {
                            selector = selector.replace('$NONEMPTY', '$EMPTY');
                        }
                        else if ('$ACTIVE' == first_part) {
                            selector = selector.replace('$ACTIVE', '$INACTIVE');
                        }
                        else if ('$INACTIVE' == first_part) {
                            selector = selector.replace('$INACTIVE', '$ACTIVE');
                        }
                    }

                    selector = selector.replace('$EMPTY', "$('.facetwp-facet.is-empty')");
                    selector = selector.replace('$NONEMPTY', "$('.facetwp-facet.not-empty')");
                    selector = selector.replace('$ACTIVE', "$('.facetwp-facet.is-active')");
                    selector = selector.replace('$INACTIVE', "$('.facetwp-facet.not-active')");

                    FWPCL.actions.push(selector + animation);
                }
                else {
                    FWPCL.actions.push(selector + animation);
                }
            });
        }
        else {
            FWPCL.actions.push("$('" + item + "')" + animation);
        }
    }

    window.FWPCL = {};

    FWPCL.run = function() {
        FWPCL.actions = [];

        buildAliases();

        // each ruleset
        $.each(FWP_JSON.rulesets, function(ruleset) {

            // skip inactive rulesets
            if ( 'undefined' !== typeof ruleset.active && false === ruleset.active) {
                return;
            }

            // if no conditions, set to TRUE
            var this_result = (ruleset.conditions.length < 1);
            var result = [];

            // foreach condition group
            $.each(ruleset.conditions, function(cond_group) {
                this_result = false;

                // foreach "OR" condition
                $.each(cond_group, function(cond_or) {
                    if (evaluateCondition(cond_or)) {
                        this_result = true;
                        return false; // exit loop
                    }
                });

                result.push(this_result);
            });

            // make sure no conditions are false
            var is_valid = (result.indexOf(false) < 0);
            var action_else = ('undefined' !== typeof ruleset.else) ? ruleset.else : 'flip';

            // apply actions
            $.each(ruleset.actions, function(action) {
                if (is_valid || 'flip' === action_else) {
                    doAction(action, is_valid);
                }
            });

            $.each(FWPCL.actions, function(action) {
                eval(action);
            });

            // custom hooks
            FWP.hooks.addAction('facetwp/ruleset/apply', {
                ruleset: ruleset,
                is_valid: is_valid,
                action_else: action_else
            });
        });
    }

    $().on('facetwp-loaded', function() {
        FWPCL.run();
    });
})(fUtil);
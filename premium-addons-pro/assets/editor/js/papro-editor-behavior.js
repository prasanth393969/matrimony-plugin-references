(function ($) {
    window.premiumEditorBehavior = function ($element, settings) {
        var self = this,
            $el = $element,
            elementSettings = settings,
            editModel = null,
            repeater = null;


        var items = $el.find(elementSettings.item),
            tag = $el.prop('tagName');

        if ($el.hasClass('e-con'))
            tag = 'SECTION';

        self.init = function () {

            editModel = self.getEditModelBycId();

            if (!items.length || undefined === editModel) {
                return;
            }

            repeater = editModel.get(elementSettings.repeater).models;

            if (elementSettings.widgets.includes("resize")) {

                var resizableOptions = self.getResizableOptions();

            }

            var draggableOptions = self.getDraggableOptions();

            if ('SECTION' !== tag) {
                var $widget = elementor.previewView.$childViewContainer.find('.elementor-widget-wrap');
                $widget.find(elementSettings.item).closest('.elementor-widget-wrap').sortable('disable');
            }


            items.filter(function () {

                if ('absolute' === $(this).css('position')) {

                    $(this).draggable(draggableOptions);

                    if (elementSettings.widgets.includes("resize")) {

                        if (!$(this).hasClass("parallax-svg"))
                            $(this).resizable(resizableOptions);

                    }

                }

            });

        };

        self.getDraggableOptions = function () {

            if ('premium_img_layers_images_repeater' === elementSettings.repeater) {
                elementor.listenTo(elementor.channels.deviceMode, 'change', function () {
                    $el.find(elementSettings.item).each(function (index, item) {
                        $(item).removeAttr("style");

                        window.PAPROWidgetsEditor.reRender(elementorFrontend.getCurrentDeviceMode());
                    });
                });

            }

            var draggableOptions = {};

            draggableOptions.stop = function (e, ui) {

                var index = self.layerToEdit(ui.helper),
                    deviceSuffix = self.getCurrentDeviceSuffix(),
                    hUnit = 'SECTION' === tag ? '%' : repeater[index].get(elementSettings.hor + deviceSuffix).unit,
                    hWidth = window.elementor.helpers.elementSizeToUnit(ui.helper, ui.position.left, hUnit),
                    vUnit = repeater[index].get(elementSettings.ver + deviceSuffix).unit,
                    vWidth = ('%' === vUnit || 'SECTION' === tag) ? self.verticalOffsetToPercent(ui.helper, ui.position.top) : window.elementor.helpers.elementSizeToUnit(ui.helper, ui.position.top, vUnit),
                    settingToChange = {};


                if (-1 !== elementSettings.repeater.indexOf('parallax')) {

                    ui.helper.removeClass("premium-parallax-center");
                    settingToChange['premium_parallax_layer_hor'] = 'custom';
                    settingToChange['premium_parallax_layer_ver'] = 'custom';

                }

                settingToChange[elementSettings.hor + deviceSuffix] = {
                    unit: hUnit,
                    size: hWidth
                };

                settingToChange[elementSettings.ver + deviceSuffix] = {
                    unit: vUnit,
                    size: vWidth
                };

                if ('SECTION' !== tag) {
                    $el.trigger('click');
                } else {
                    $el.find('i.eicon-handle').eq(0).trigger('click');
                }

                window.PAPROWidgetsEditor.activateEditorPanelTab(elementSettings.tab);

                repeater[index].setExternalChange(settingToChange);

            };

            return draggableOptions;

        };

        self.getResizableOptions = function () {

            var resizableOptions = {};

            resizableOptions.handles = self.setHandle();
            resizableOptions.stop = function (e, ui) {

                var index = self.layerToEdit(ui.element),
                    deviceSuffix = self.getCurrentDeviceSuffix(),
                    unit = 'SECTION' === tag ? '%' : repeater[index].get(elementSettings.width + deviceSuffix).unit,
                    width = window.elementor.helpers.elementSizeToUnit(ui.element, ui.size.width, unit),
                    settingToChange = {};

                settingToChange[elementSettings.width + deviceSuffix] = {
                    unit: unit,
                    size: width
                };

                if ('SECTION' !== tag) {
                    $el.trigger('click');
                } else {
                    $el.find('i.eicon-handle').eq(0).trigger('click');
                }

                window.PAPROWidgetsEditor.activateEditorPanelTab(elementSettings.tab);

                repeater[index].setExternalChange(settingToChange);

            };

            return resizableOptions;

        };

        self.getModelcId = function () {

            return $el.closest('.elementor-element').data('model-cid');

        };

        self.getEditModelBycId = function () {

            var cID = self.getModelcId();

            return elementorFrontend.config.elements.data[cID];

        };

        self.getCurrentDeviceSuffix = function () {

            var currentDeviceMode = elementorFrontend.getCurrentDeviceMode();

            return ('desktop' === currentDeviceMode) ? '' : '_' + currentDeviceMode;

        };

        self.layerToEdit = function ($layer) {

            var offset = elementSettings.offset;

            if ('SECTION' === tag && !$el.hasClass("premium-lottie-yes")) {
                var length = $el.find(elementSettings.item).length;

                if (length > 1) {
                    return (length - 1) - $el.find($layer).index();
                }
            }

            return ($el.find($layer).index()) - offset;

        };

        self.verticalOffsetToPercent = function ($el, size) {

            size = size / ($el.offsetParent().height() / 100);

            return Math.round(size * 1000) / 1000;

        };

        self.setHandle = function () {

            return window.elementor.config.is_rtl ? 'w' : 'e';

        };

    };
}(jQuery));

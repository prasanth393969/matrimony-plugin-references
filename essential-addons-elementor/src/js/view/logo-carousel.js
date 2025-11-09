var LogoCarouselHandler = function($scope, $) {

	const items_tablet = getControlValue('items_tablet', $scope);
	const items_mobile = getControlValue('items_mobile', $scope);

	var $carousel = $scope.find(".eael-logo-carousel").eq(0),
		$defaultItems =
			$carousel.data("items") !== undefined ? $carousel.data("items") : 3,
		$items_tablet =
			items_tablet !== undefined
				? items_tablet
				: 3,
		$items_mobile =
			items_mobile !== undefined
				? items_mobile
				: 3,
		$defaultMargin =
			$carousel.data("margin") !== undefined
				? $carousel.data("margin")
				: 10,
		$margin_tablet =
			$carousel.data("margin-tablet") !== undefined
				? $carousel.data("margin-tablet")
				: 10,
		$margin_mobile =
			$carousel.data("margin-mobile") !== undefined
				? $carousel.data("margin-mobile")
				: 10,
		$effect =
			$carousel.data("effect") !== undefined
				? $carousel.data("effect")
				: "slide",
		$speed =
			$carousel.data("speed") !== undefined
				? $carousel.data("speed")
				: 400,
		$autoplay =
			$carousel.data("autoplay") !== undefined
				? $carousel.data("autoplay")
				: 999999,
		$loop =
			$carousel.data("loop") !== undefined ? $carousel.data("loop") : 0,
		$grab_cursor =
			$carousel.data("grab-cursor") !== undefined
				? $carousel.data("grab-cursor")
				: 0,
		$pagination =
			$carousel.data("pagination") !== undefined
				? $scope.find($carousel.data("pagination")).get(0)
				: $scope.find(".swiper-pagination").get(0),
		$arrow_next =
			$carousel.data("arrow-next") !== undefined
				? $scope.find($carousel.data("arrow-next")).get(0)
				: $scope.find(".swiper-button-next").get(0),
		$arrow_prev =
			$carousel.data("arrow-prev") !== undefined
				? $scope.find($carousel.data("arrow-prev")).get(0)
				: $scope.find(".swiper-button-prev").get(0),
		$pause_on_hover =
			$carousel.data("pause-on-hover") !== undefined
				? $carousel.data("pause-on-hover")
				: "",
		$carousel_options = {
			direction: "horizontal",
			speed: $speed,
			effect: $effect,
			grabCursor: $grab_cursor,
			paginationClickable: true,
			autoHeight: true,
			loop: $loop,
			observer: true,
			observeParents: true,
			autoplay: {
				delay: $autoplay,
				disableOnInteraction: false,
			},
			pagination: {
				el: $pagination,
				clickable: true
			},
			navigation: {
				nextEl: $arrow_next,
				prevEl: $arrow_prev
			}
		};

		if($effect === 'slide' || $effect === 'coverflow') {
			if (typeof (localize.el_breakpoints) === 'string') {
				$carousel_options.breakpoints = {
					1024: {
						slidesPerView: $defaultItems,
						spaceBetween: $defaultMargin
					},
					768: {
						slidesPerView: $items_tablet,
						spaceBetween: $margin_tablet
					},
					320: {
						slidesPerView: $items_mobile,
						spaceBetween: $margin_mobile
					}
				};
			} else {
				let el_breakpoints = {}, breakpoints = {}, bp_index = 0,
					desktopBreakPoint = localize.el_breakpoints.widescreen.is_enabled ? localize.el_breakpoints.widescreen.value - 1 : 4800;
				el_breakpoints[bp_index] = {
					breakpoint: 0,
					slidesPerView: 0,
					spaceBetween: 0
				}
				bp_index++;
				localize.el_breakpoints.desktop = {
					is_enabled: true,
					value: desktopBreakPoint
				}
				$.each(['mobile', 'mobile_extra', 'tablet', 'tablet_extra', 'laptop', 'desktop', 'widescreen'], function (index, device) {
					let breakpoint = localize.el_breakpoints[device];
					if (breakpoint.is_enabled) {
						let _items = getControlValue('items_' + device, $scope),
							_margin = $carousel.data('margin-' + device);
						$margin = _margin !== undefined ? _margin : (device === 'desktop' ? $defaultMargin : 10);
						$items = _items !== undefined && _items !== "" ? _items : (device === 'desktop' ? $defaultItems : 3);
						el_breakpoints[bp_index] = {
							breakpoint: breakpoint.value,
							slidesPerView: $items,
							spaceBetween: $margin
						}
						bp_index++;
					}
				});

				$.each(el_breakpoints, function (index, breakpoint) {
					let _index = parseInt(index);
					if (typeof el_breakpoints[_index + 1] !== 'undefined') {
						breakpoints[breakpoint.breakpoint] = {
							slidesPerView: el_breakpoints[_index + 1].slidesPerView,
							spaceBetween: el_breakpoints[_index + 1].spaceBetween
						}
					}
				});

				$carousel_options.breakpoints = breakpoints;
			}
		}else {
			$carousel_options.items = 1;
		}

	swiperLoader($carousel, $carousel_options).then((LogoCarousel)=>{
		if ($pause_on_hover) {
			$carousel.on("mouseenter", function() {
				LogoCarousel.autoplay.stop();
			});
			$carousel.on("mouseleave", function() {
				LogoCarousel.autoplay.start();
			});
		}

		LogoCarouselToolTip($scope, $);
	});

	let LogoCarouselLoader = function (element) {
		// Add a small delay to ensure tab content is fully visible
		setTimeout(function() {
			let logoCarousels = $(element).find('.eael-logo-carousel');
			if (logoCarousels.length) {
				logoCarousels.each(function () {
					let $currentCarousel = $(this);
					let $currentScope = $currentCarousel.closest('.elementor-widget');

					// Destroy existing swiper instance if it exists
					if ($currentCarousel[0].swiper) {
						$currentCarousel[0].swiper.destroy(true, true);
					}

					// Reinitialize with proper scope and configuration
					if ($currentScope.length) {
						LogoCarouselHandler($currentScope, $);
					}
				});
			}
		}, 100); // 100ms delay to ensure content is rendered
	}

	eael.hooks.addAction("ea-lightbox-triggered", "ea", LogoCarouselLoader);
	eael.hooks.addAction("ea-advanced-tabs-triggered", "ea", LogoCarouselLoader);
	eael.hooks.addAction("ea-advanced-accordion-triggered", "ea", LogoCarouselLoader);

	$(window).on('resize', function(){
		$carousel = $('body').find(".eael-logo-carousel");
		if( $carousel.length > 0 ) {
			$carousel.each(function () {
				$scope = $(this).closest('.elementor-widget');
				if ($(this)[0].swiper) {
					$(this)[0].swiper.destroy(true, true);
				}
				LogoCarouselHandler($scope, $);
			});
		}
	});
};

const swiperLoader = (swiperElement, swiperConfig) => {
	if ('undefined' === typeof Swiper || 'function' === typeof Swiper) {
		const asyncSwiper = elementorFrontend.utils.swiper;
		return new asyncSwiper(swiperElement, swiperConfig).then((newSwiperInstance) => {
			return newSwiperInstance;
		});
	} else {
		return swiperPromise(swiperElement, swiperConfig);
	}
}

const swiperPromise =  (swiperElement, swiperConfig) => {
	return new Promise((resolve, reject) => {
		const swiperInstance =  new Swiper( swiperElement, swiperConfig );
		resolve( swiperInstance );
	});
}

/**
 * getControlValue
 *
 * Return Elementor control value in frontend,
 * But before uses this method you have to ensure that,
 * "frontend_available = true" in elementor control
 *
 * @since 5.0.1
 * @param name
 * @param $scope
 * @returns {*}
 */
const getControlValue = (name, $scope) => {
	if (eael.isEditMode) {
		return elementorFrontend.config.elements?.data[$scope[0]?.dataset.modelCid]?.attributes[name]?.size;
	} else {
		return $scope?.data('settings')?.[name]?.size;
	}
}

var LogoCarouselToolTip = function($scope, $) {
    if ($.fn.tooltipster) {
		var duplicates = $scope.find('.swiper-slide-duplicate');
		if(duplicates.length > 0 ){
			$.each(duplicates, function(index, item){
				let id = $(item).find('.eael-lc-tooltip').attr('id');
				$(item).find('.eael-lc-tooltip').attr('id', id+'-duplicate-'+index);
			});
		}

        var $tooltips = $scope.find(".eael-lc-tooltip");
		if( $tooltips.length > 0 ){
			$.each($tooltips, function(index, tooltip){
				let $tooltip 	 	= $(tooltip),
					$tooltipContent = $tooltip.data("content") !== undefined ? $tooltip.data("content") : null,
				 	$tooltipSide 	= $tooltip.data("side") !== undefined ? $tooltip.data("side") : false,
					$tooltipTrigger = $tooltip.data("trigger") !== undefined ? $tooltip.data("trigger") : "hover",
					$animation 		= $tooltip.data("animation") !== undefined ? $tooltip.data("animation") : "fade",
					$anim_duration 	= $tooltip.data("animation_duration") !== undefined ? $tooltip.data("animation_duration") : 300,
					$theme 			= $tooltip.data("theme") !== undefined ? $tooltip.data("theme") : "default",
					$arrow 	= "yes" == $tooltip.data("arrow") ? true : false;

				$tooltip.tooltipster({
					animation: $animation,
					trigger: $tooltipTrigger,
					content: DOMPurify.sanitize($tooltipContent),
					contentAsHTML: true,
					side: $tooltipSide,
					delay: $anim_duration,
					arrow: $arrow,
					contentCloning: true,
					theme: "tooltipster-" + $theme
				});
			});
		}
    }
};

jQuery(window).on("elementor/frontend/init", function() {

	if (eael.elementStatusCheck('eaelLogoSliderLoad')) {
		return false;
	}

	elementorFrontend.hooks.addAction(
		"frontend/element_ready/eael-logo-carousel.default",
		LogoCarouselHandler
	);

	// elementorFrontend.hooks.addAction(
    //     "frontend/element_ready/eael-logo-carousel.default",
    //     LogoCarouselToolTip
    // );
});


var MulticolumnPricingTable = function($scope, $) {
	let wrapper = $scope.find('.eael-multicolumn-pricing-table-wrapper');
    
    if( wrapper.hasClass( 'collapsable' ) ) {
        let row_cout = wrapper.data('row');
            row_cout = row_cout ? row_cout : 3;

            $(document).on('click', '.eael-mcpt-collaps', function(e){
                $this = $(this);
                $this.toggleClass('collapsed');

                if( ! $this.hasClass('collapsed') ) {
                    $('.eael-mcpt-cell', wrapper).removeClass('hide');
                    $('.eael-mcpt-collaps-label.collaps').removeClass('show');
                    $('.eael-mcpt-collaps-label.open').addClass('show');
                } else {
                    $('.eael-mcpt-collaps-label.open').removeClass('show');
                    $('.eael-mcpt-collaps-label.collaps').addClass('show');
                   $('.eael-mcpt-column', $scope ).each(function(index, column){
                        var cells = $(column).find('.eael-mcpt-cell');
                        cells.each(function(index, cell){
                            if( index > row_cout ){
                                $(this).addClass('hide');
                            }
                        });
                    });
                    
                    $this.removeClass('hide');
                }
            });
    }
};
jQuery(window).on("elementor/frontend/init", function() {

	if (eael.elementStatusCheck("multicolumnPricingTable")) {
		return false;
	}

	elementorFrontend.hooks.addAction(
		"frontend/element_ready/eael-multicolumn-pricing-table.default",
		MulticolumnPricingTable
	);
});

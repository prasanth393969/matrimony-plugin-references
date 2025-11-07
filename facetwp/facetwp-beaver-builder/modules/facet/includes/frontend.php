<?php

echo '<div class="facetwp-bb-module">';

if ( ! empty( $settings->title ) ) {
	echo '<h4 class="facetwp-facet-title">' . esc_html( $settings->title ) . '</h4>';
}

echo facetwp_display( 'facet', $settings->facet );
echo '</div>';

if ( FLBuilderModel::is_builder_active() ) {
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	if ('undefined' !== typeof FWP) {
		FWP.template = 'wp';
		FWP.extras.bb_node = jQuery('.facetwp-template').data('node');
		FWP.refresh();
	}
});
</script>
<?php
}

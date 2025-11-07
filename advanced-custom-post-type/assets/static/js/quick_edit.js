jQuery( function( $ ){

    const wp_inline_edit_function = inlineEditPost.edit;

    // we overwrite the it with our own
    inlineEditPost.edit = function( post_id ) {

        // let's merge arguments of the original function
        wp_inline_edit_function.apply( this, arguments );

        // get the post ID from the argument
        if ( typeof( post_id ) == 'object' ) { // if it is object, get the ID number
            post_id = parseInt( this.getId( post_id ) );
        }

        if(typeof( post_id ) !== 'number') {
            return;
        }

        const post_row = $( '#edit-' + post_id );

        post_row.find('*[data-acpt-column]').each(function() {

            const $field = $(this);
            const $fieldName = $field.data("acpt-column").replace("column-", "");
            const $fieldTag = $field[0].tagName;
            const baseAjaxUrl = (typeof ajaxurl === 'string') ? ajaxurl : '/wp-admin/admin-ajax.php';

            $.ajax({
                type: 'POST',
                url: baseAjaxUrl,
                data: {
                    "action": "fetchMetaValueAction",
                    "data": JSON.stringify({
                        "fieldName": $fieldName,
                        "objectType": "customPostType",
                        "objectId": post_id,
                    }),
                },
                success: function(data) {

                    switch ($fieldTag) {

                        // Checkbox
                        // Radio
                        case "DIV":

                            $field.find("input").each(function(){
                                const $input = $(this);
                                const value = $input.val();

                                if(data.value === null){
                                    $input.prop("checked", false);
                                } else if(typeof data.value === 'object'){
                                    if(data.value.includes(value)){
                                        $input.prop("checked", true);
                                    } else {
                                        $input.prop("checked", false);
                                    }
                                } else if(typeof data.value === 'string'){
                                    if(value === data.value){
                                        $input.prop("checked", true);
                                    } else {
                                        $input.prop("checked", false);
                                    }
                                }
                            });

                            break;

                        // Input
                        default:
                        case "INPUT":
                            if(data.value){
                                $field.val(data.value)
                            } else {
                                $field.val("");
                            }
                            break;

                        // Select
                        // Select multiple
                        case "SELECT":

                            const $isMulti = $field.attr("multiple");

                            if($isMulti){

                                $field.find("option").each(function(){
                                    const $option = $(this);
                                    const value = $option.val();

                                    if(data.value === null){
                                        $option.prop("selected", false);
                                    } else if(typeof data.value === 'object'){
                                        if(data.value.includes(value)){
                                            $option.prop("selected", true);
                                        } else {
                                            $option.prop("selected", false);
                                        }
                                    } else if(typeof data.value === 'string'){
                                        if(value === data.value){
                                            $option.prop("selected", true);
                                        } else {
                                            $option.prop("selected", false);
                                        }
                                    }
                                });
                            } else {
                                if(data.value){
                                    $field.val(data.value)
                                } else {
                                    $field.val("");
                                }
                            }

                            break;
                    }
                },
                dataType: 'json'
            });


        });
    }
});

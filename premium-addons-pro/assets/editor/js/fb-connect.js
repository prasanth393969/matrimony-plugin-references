var licenseKey = settings.key;

function openPaProConnectPopup(url, width, height, callBack, widget) {
    var top = top || screen.height / 2 - height / 2,
        left = left || screen.width / 2 - width / 2,
        win = window.open(
            url,
            "",
            "location=1,status=1,resizable=yes,width=" +
            width +
            ",height=" +
            height +
            ",top=" +
            top +
            ",left=" +
            left
        );

    function check() {

        if (!win || win.closed != false) {
            if ('instagram' !== widget) {
                callBack();
            } else {
                setTimeout(function () {
                    console.log("Triggered");
                    callBack();
                }, 18000);
            }

        } else {
            setTimeout(check, 1000);
        }
    }

    setTimeout(check, 1000);
}

function connectFb(obj, type) {

    var url = "https://appfb.premiumaddons.com/auth/fbreviews?scope=manage_pages,pages_show_list"

    url = url + "&key=" + licenseKey;

    openPaProConnectPopup(
        url, 670, 520,
        function () {
            jQuery.ajax({
                type: "GET",
                url: settings.ajaxurl,
                dataType: "JSON",
                data: {
                    action: "get_fb_page_token",
                    security: settings.nonce
                },
                success: function (res) {
                    if (res.success) {
                        var accessID =
                            "reviews" === type ?
                                "page_access" :
                                "access_token",
                            pageID =
                                "reviews" === type ? "page_id" : "account_id";

                        if (undefined !== res.data.license) {

                            jQuery(obj)
                                .parents(".elementor-control-facebook_login")
                                .nextAll(".elementor-control-" + accessID)
                                .find("textarea")
                                .val("Invalid License Key")
                                .trigger("input");

                            return;

                        }

                        var accessToken = res.data.access_token,
                            name = res.data.name,
                            id = res.data.id;

                        if ("reviews" === type)
                            jQuery(obj)
                                .parents(".elementor-control-facebook_login")
                                .nextAll(".elementor-control-page_name")
                                .find("input")
                                .val(name)
                                .trigger("input");

                        jQuery(obj)
                            .parents(".elementor-control-facebook_login")
                            .nextAll(".elementor-control-" + pageID)
                            .find("input")
                            .val(id)
                            .trigger("input");

                        jQuery(obj)
                            .parents(".elementor-control-facebook_login")
                            .nextAll(".elementor-control-" + accessID)
                            .find("textarea")
                            .val(accessToken)
                            .trigger("input");
                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    );

    return false;
}

function connectInstagram(obj) {

    var url = "https://appfb.premiumaddons.com/auth/instagram?"

    url = url + "key=" + licenseKey;

    openPaProConnectPopup(
        url, 670, 520,
        function () {

            jQuery.ajax({
                type: "GET",
                url: settings.ajaxurl,
                dataType: "JSON",
                data: {
                    action: "get_instagram_token",
                    security: settings.nonce
                },
                success: function (res) {
                    if (res.success) {

                        if (undefined !== res.data.license) {

                            jQuery(obj)
                                .parents(".elementor-control-instagram_login")
                                .nextAll(".elementor-control-new_accesstoken")
                                .find("textarea")
                                .val("Invalid License Key")
                                .trigger("input");

                            return;

                        }

                        var accessToken = res.data;

                        jQuery(obj)
                            .parents(".elementor-control-instagram_login")
                            .nextAll(".elementor-control-new_accesstoken")
                            .find("textarea")
                            .val(accessToken)
                            .trigger("input");

                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        },
        'instagram'
    );
}

function connectTwitter(obj) {

    var url = "https://appfb.premiumaddons.com/auth/twitter?"

    url = url + "key=" + licenseKey;

    openPaProConnectPopup(
        url, 670, 520,
        function () {

            jQuery.ajax({
                type: "GET",
                url: settings.ajaxurl,
                dataType: "JSON",
                data: {
                    action: "get_twitter_token",
                    security: settings.nonce
                },
                success: function (res) {
                    if (res.success) {

                        if (undefined !== res.data.license) {

                            jQuery(obj)
                                .parents(".elementor-control-twitter_login")
                                .nextAll(".elementor-control-access_token")
                                .find("textarea")
                                .val("Invalid License Key")
                                .trigger("input");

                            return;

                        }

                        var accessToken = res.data;

                        jQuery(obj)
                            .parents(".elementor-control-twitter_login")
                            .nextAll(".elementor-control-access_token")
                            .find("textarea")
                            .val(accessToken)
                            .trigger("input");

                    }
                },
                error: function (err) {
                    console.log(err);
                }
            });
        }
    );
}

function connectTwitterInit(obj) {

    if (!obj) return;

    var type = jQuery(obj).data("type");

    connectTwitter(obj, type);
}

function connectFbInit(obj) {

    if (!obj) return;

    var type = jQuery(obj).data("type");

    connectFb(obj, type);
}

function connectInstagramInit(obj) {

    if (!obj) return;

    var type = jQuery(obj).data("type");

    connectInstagram(obj, type);
}
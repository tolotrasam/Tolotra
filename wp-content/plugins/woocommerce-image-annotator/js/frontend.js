jQuery(document).ready(function ($) {

    var prev_post_title, prev_thumbnail, next_post_title, next_thumbnail;

    //Browser resize 
    $(window).resize(function () {
        var height = $('#wcia_remodal').height();
        $('#wcia_contend .summary').css('height', height);
        var scrollable = document.getElementsByClassName('scrollable')[0];
        if (scrollable) {
            if ((scrollable.scrollHeight > scrollable.clientHeight) === true) {
                $('.scrollbar_bg').css('height', height);
                $('.scrollbar_bg').show();
            } else {
                $('.scrollbar_bg').hide();
            }
        }
    });

    //remodel js open 
    $(document).on('opened', '#wcia_remodal', function () {
        $('body').css('overflow', 'hidden');
        $('.spinner').remove();
    });

    //remodel js closed 
    $(document).on('closed', '#wcia_remodal', function () {
        $('body').css('overflow', 'auto');
    });

    //shop page button click 
    $(document).on('click', ".wcia_quick_view", function () {
        var product_id = $(this).data('product-id');
        var picture_id = $(this).data('picture-id');

        wcia_get_product_details(picture_id, product_id);
        $(this).append('<div class="spinner"></div>')

    });

    //woocommerce gallery 
    $(document).on('click', '#wcia_contend .thumbnails a', function (e) {

        e.preventDefault();
        var img_url = $(this).attr('href');
        var img_src = $(this).find('img').attr('srcset');
        $('.woocommerce-main-image').find('img').attr('src', img_url);
        $('.woocommerce-main-image').find('img').attr('srcset', img_src);
        $('.woocommerce-main-image').closest('a').attr('href', img_url);

        $("a.zoom").prettyPhoto({
            hook: 'data-rel',
            social_tools: false,
            theme: 'pp_woocommerce',
            horizontal_padding: 20,
            opacity: 0.8,
            deeplinking: false
        });
        $("a[data-rel^='prettyPhoto']").prettyPhoto({
            hook: 'data-rel',
            social_tools: false,
            theme: 'pp_woocommerce',
            horizontal_padding: 20,
            opacity: 0.8,
            deeplinking: false
        });

    });


    //scrolling enable or not
    $(document).on('mouseenter', "#wcia_contend .summary", function () {
        var scrollable = document.getElementsByClassName('scrollable')[0];
        if ((scrollable.scrollHeight > scrollable.clientHeight) === true) {
            var $scrollable = $('.scrollable'),
                $scrollbar = $('.scrollbar'),
                H = $scrollable.outerHeight(true),
                sH = $scrollable[0].scrollHeight,
                sbH = H * H / sH;

            $scrollbar.height(sbH).hide();

            $scrollable.on("scroll", function () {

                $scrollbar.css({top: $scrollable.scrollTop() / H * sbH});
            });
            $('.scrollbar').show();
        }
    });

    $(document).on('mouseleave', "#wcia_contend .summary", function (scrollable) {
        var scrollable = document.getElementsByClassName('scrollable')[0];
        if ((scrollable.scrollHeight > scrollable.clientHeight) === true) {

            $('.scrollbar').hide();
        }
    });


    //hover previous button
    $(document).on('mouseenter', ".wcia_prev", function () {
        if ($('.wcia_prev_title').length === 0) {
            $(this).append('<div class="wcia_prev_title"><h4>' + prev_post_title + '</h4></div>');
            $(this).append('<div class="wcia_prev_thumbnail"></div>');
            $('.wcia_prev_thumbnail').html(prev_thumbnail);

        }
    });

    $(document).on('mouseleave', ".wcia_prev", function () {
        if ($('.wcia_prev_title').length !== 0) {
            $(this).removeClass('wcia_prev_title');
            $('.wcia_prev_title').remove();
            $('.wcia_prev_thumbnail').remove();

        }
    });

    $(document).on('click', ".wcia_prev", function () {

        var product_id = $(this).data('data-prev-post');
        wcia_get_product_details(product_id);
    });

    //hover next button
    $(document).on('mouseenter', ".wcia_next", function () {
        if ($('.wcia_next_title').length === 0) {
            $(this).append('<div class="wcia_next_thumbnail"></div>');
            $(this).append('<div class="wcia_next_title"><h4>' + next_post_title + '</h4></div>');
            $('.wcia_next_thumbnail').html(next_thumbnail);

        }
    });
    $(document).on('mouseleave', ".wcia_next", function () {
        if ($('.wcia_next_title').length !== 0) {
            $(this).removeClass('wcia_next_title');
            $('.wcia_next_title').remove();
            $('.wcia_next_thumbnail').remove();

        }
    });

    $(document).on('click', ".wcia_next", function () {

        var picture_id = $(this).data('data-next-post');
        var product_id = $(this).data('data-next-post');
        wcia_get_product_details(picture_id, product_id);
    });

    function set_up_mouseover_listener() {

        $(document).on('mouseenter', ".product.left", function () {
            
            var product_order = $(this).data('order');
            var target_element = $('.annotated-image-wrapper').find('[data-order="' + product_order + '"]');
            var target_element_a = $('.annotated-image-wrapper').find('[data-order="' + product_order + '"]').find('.order_indicator');

            target_element.css({"border": "solid 3px rgba(255, 255, 255, 0.68)"});
            target_element_a.css({"opacity": "1"});

            $(this).animate({ top:  "-15px"});

        });
        $(document).on('mouseleave', ".product.left", function () {
            var product_order = $(this).data('order');
            var target_element = $('.annotated-image-wrapper').find('[data-order="' + product_order + '"]');
            var target_element_a = $('.annotated-image-wrapper').find('[data-order="' + product_order + '"]').find('.order_indicator');

            target_element.css({"border": "solid 3px rgba(255, 255, 255, 0)"});
            target_element_a.css({"opacity": "0"});

            $(this).animate({ top:  "+15px"});
        });
    }

    set_up_mouseover_listener(); //when the user hover above the product


    function wcia_get_product_details(picture_id, product_id) {

        var container = $('#wcia_contend');

        if (picture_id !== undefined) {
            jQuery.ajax({
                type: 'POST',
                url: wcia_frontend_obj.ajaxurl,
                data: {
                    'action': 'wcia_get_image_products',
                    'product_id': product_id,
                    'picture_id': picture_id,

                },
                success: function (response) {



                    $('#wcia_contend').html(response);
                    $('#wcia_contend .summary').addClass('scrollable');
                    $('#wcia_remodal').show();

                    var prev_post_id = $('.wcia_prev_data').data('wcia-prev-id');
                    var next_post_id = $('.wcia_next_data').data('wcia-next-id');
                    prev_post_title = $('.wcia_prev_data').text();
                    next_post_title = $('.wcia_next_data').text();
                    var prev_src = ($('.wcia_prev_data>img').length !== 0) ? $('.wcia_prev_data>img').attr('src') : '';
                    var nex_src = ($('.wcia_next_data>img').length !== 0) ? $('.wcia_next_data>img').attr('src') : '';
                    prev_thumbnail = '<img src = "' + prev_src + '">';
                    next_thumbnail = '<img src = "' + nex_src + '">';

                    if (($('.wcia_prev').length === 0) && (prev_post_id !== '')) {

                        $('.remodal-wrapper').prepend('<div class="wcia_prev wrapper" data-prev-post=' + prev_post_id + ' style="display:block;left:0;"><div class="icon"></div></div>');

                    }

                    if (($('.wcia_next').length === 0) && (next_post_id !== '')) {

                        $('.remodal-wrapper').prepend('<div class="wcia_next wrapper" data-next-post=' + next_post_id + ' style="display:block;right:0;"><div class="icon"></div></div>');

                    }

                    $('.wcia_prev').data('data-prev-post', prev_post_id);
                    $('.wcia_prev_title').html('<h4>' + prev_post_title + '</h4>');
                    $('.wcia_prev_thumbnail').html(prev_thumbnail);

                    $('.wcia_next').data('data-next-post', next_post_id);
                    $('.wcia_next_title').html('<h4>' + next_post_title + '</h4>');
                    $('.wcia_next_thumbnail').html(next_thumbnail);


                    if (prev_post_id === '') {
                        $('.wcia_prev').remove();
                    }
                    if (next_post_id === '') {
                        $('.wcia_next').remove();
                    }

                    //open modal
                    var inst = $('[data-remodal-id=wcia_remodal]').remodal();

                    var state = inst.getState();
                    if (state == 'closed') {
                        inst.open();
                    }


                    var height = $('#wcia_remodal').height();
                    $('#wcia_contend .summary').css('height', height);

                    //sroll
                    var color = $('#wcia_remodal').css('background-color');

                    $('#wcia_contend .scrollbar_bg').css('background', color);
                    $('#wcia_contend .scrollbar_bg').html('<div class="scrollbar"></div>');
                    var height = $('#wcia_remodal').height();
                    $('.scrollbar_bg').css('height', height);
                    var scrollable = document.getElementsByClassName('scrollable')[0];
                    if ((scrollable.scrollHeight > scrollable.clientHeight) === false) {
                        $('.scrollbar_bg').hide();
                    }
                    //end scroll
                }
            });


        } else {
            console.log("script stopped, no image-data");
        }

    }

});
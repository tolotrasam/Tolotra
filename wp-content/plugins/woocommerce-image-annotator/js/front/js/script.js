(function ($) {
    $(function () {
       // console.log("loaded again");


    });
})(jQuery);

init_canvas();

function init_canvas() {

    //console.log("init canvas");
    function resizeCanvas(canvas, image, originalSize) {
        canvas.setWidth(image.width());
        canvas.setHeight(image.height());

        var factor = image.width() / originalSize[0];
        canvas.setZoom(factor);
    }

    function makePolygon(point1, point2, point3, canvas, groupID) {

        var deleteFirst = getItemById(groupID, 'polygon-two', canvas);
        canvas.remove(deleteFirst);

        var shape = new fabric.PolygonTwo([point1, point2, point3], {
            fill: 'red',
            hasControls: false,
            lockRotation: true,
            selection: false,
            selectable: false,
            padding: -1,
            perPixelTargetFind: true,
            id: groupID
        });

        return shape;
    }

    function getItemById(id, type, canvas) {
        var correctObject;

        canvas.forEachObject(function (obj) {
            if ((obj.type === type) && (obj.id === id)) {
                correctObject = obj;
            }
        });

        return correctObject;
    }

    jQuery(document).ready(function ($) {
        //tolotra load div tags

        var initial_object_data;
        var add_annotation_obj_id_number = 0;
        var object_positions = {};
        var array_positions = [];

        function createSpanMarkerElement() {
            var a = document.createElement('a');
            a.setAttribute("id", "draggable4");
            a.setAttribute("data-id", add_annotation_obj_id_number);
            a.setAttribute("class", "img-annotator-marker-url");

            var number = document.createElement('div');

            number.setAttribute("id", "urlproduct");
            number.setAttribute("class", "order_indicator");
            a.appendChild(number);
            return a;
        };
        function tolotra_set_style(a, object_to_load) {
            a = $(a);
            //loading from initial
            if (typeof (object_to_load) != 'undefined') {
                //console.log(object_to_load);
                a.css({
                    "style": object_to_load.top * 100 + "%",
                    "top": object_to_load.top * 100 + "%",
                    "left": object_to_load.left * 100 + "%",
                    "width": object_to_load.width * 100 + "%",
                    "height": object_to_load.height * 100 + "%",
                });

                a.attr("data-id", object_to_load.id);
            }
            return a;
        }

        function loadInitialObjects() {
            if (typeof currentWIPAObjectData !== 'undefined') {
                initial_object_data = (currentWIPAObjectData[0]);
                return initial_object_data;
            } else {
                currentWIPAObjectData = [];
            }
        }

        function tolotra_append_toScreen(span) {
            var parent = $('.annotated-image-wrapper');
            parent.append(span);
        }

        //used by both loading data when already exists and new added element
        function tolotra_load_order(element, object_to_load) {
            if (typeof (object_to_load) != 'undefined') {
                element.find('div').text(object_to_load.order);
                element.attr("data-order", object_to_load.order);
            }
        }


        function tolotra_load_url(element) {
            var product_order = element.data('order');
            var model_element = $('#product-container').find('[data-order="' + product_order + '"]').find('.wc-url-product'); //a
            var url_model_from_element = model_element.attr("href");
            element.attr("href", url_model_from_element);
        }

        function set_up_mouseover_listener() {
            $(document).on('mouseenter', ".img-annotator-marker-url", function () {
                var product_order = $(this).data('order');
                var target_element = $('#product-container').find('[data-order="' + product_order + '"]');
                target_element.stop().animate({ top:  "-10px"}, 'fast');
                $(this).css({"border": "solid 3px rgba(255, 255, 255, 0.68)"});
                $('.order_indicator').css({"opacity": "1"});

            });
            $(document).on('mouseleave', ".img-annotator-marker-url", function () {
                var product_order = $(this).data('order');
                var target_element = $('#product-container').find('[data-order="' + product_order + '"]');
                target_element.stop().animate({ top:  "+10px"}, 'fast');
                $(this).css({"border": "solid 3px rgba(255, 255, 255, 0)"});
                $('.order_indicator').css({"opacity": "0"});
            });

            $(document).on('mouseenter', ".annotated-image", function () {
                $('.order_indicator').css({"opacity": "1"});
            });
            $(document).on('mouseleave', ".annotated-image", function () {
                $('.order_indicator').css({"opacity": "0"});
            });


        }

        function tolotra_draw_loaded_data() {
            object_positions = loadInitialObjects();
            if (typeof (object_positions) != 'undefined') {
                add_annotation_obj_id_number = object_positions.last_unique_id;
                array_positions = object_positions.objects;
                //console.log(object_positions);
                //console.log(add_annotation_obj_id_number);
                for (var the_object in array_positions) {
                    if (array_positions.hasOwnProperty(the_object)) {
                        var a = createSpanMarkerElement();
                        a = tolotra_set_style(a, array_positions[the_object]); //convert js element to jquery element
                        tolotra_append_toScreen(a);
                        tolotra_load_order(a, array_positions[the_object]);
                        tolotra_load_url(a); //assign an url to a element
                        set_up_mouseover_listener(a); //set Listneres
                    }
                }
            } else {
                array_positions = [];
            }

        }

        tolotra_draw_loaded_data();


        //tolotra end div tags
        // Code that uses jQuery's $ can follow here.

        var allImages = $('.annotated-image-wrapper');


        allImages.each(function (index) {

            var image = $(this).find('.annotated-image');
            var canvas = $(this).find('canvas');
            var jsonToLoad = wciaGeneratedImage[$(this).data('wcia')];
            var originalSize = $(this).data('wcia-originalsize');
            var wcia = $(this).data('wcia');
            //console.log($(this).data('wcia'));



        });

    });

}
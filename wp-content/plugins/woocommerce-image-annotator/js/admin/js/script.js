/**
 * WP Image Annotator
 * @author Tolotra Samuel
 * Contact: Tolotra Samuel
 */


jQuery(document).ready(function ($) {


    //In case you want to change the rotation lock just update these variables
    var rotationLocked = true;
    var hasRotationPoint = false;

    //Variable to change when shift button is pressed
    shifted = false;

    var edit_mode = false;

    //Canvas wrapper
    var wciaCanvasWrapper = $('.wcia-canvas-area');

    //Canvas and images
    var imageToAnnotate = $('#wcia-preview-image');
    var wciaCanvas = wciaCanvasWrapper.find('canvas');
    if (wciaCanvasWrapper.length !== 0) {
        edit_mode = true;
    }else {
        edit_mode = false;
    }
        //Buttons
        var rectangleButton = $('.rectangle-button'),
            circleButton = $('.circle-button'),
            lineButton = $('.line-button'),
            arrowButton = $('.arrow-button'),
            textButton = $('.text-button'),
            speechBubbleButton = $('.speech-bubble-button'),
            toolButton = $('.tool-button-wcia'),
            selectButton = $('.select-button'),
            // removeButton = $('.remove-button'),
            removeButton = $('.remove-button-wcia'),
            addAnnotationButton = $('.add-button');


    function resizeCanvas(canvas) {
        canvas.setWidth(imageToAnnotate.width());
        canvas.setHeight(imageToAnnotate.height());

        if (typeof currentWIPAObject[1] !== 'undefined') {
            var factor = $('#wcia-preview-image').width() / currentWIPAObject[1][0];

            canvas.setZoom(factor);

            canvas.renderAll();
            storeCanvasToInput(canvas);
        }
    }

    function loadInitialCanvas(canvas) {
        if (typeof currentWIPAObject[1] !== 'undefined') {
            canvas.loadFromJSON(JSON.stringify(currentWIPAObject[0]));
            canvas.renderAll();
        }
    }

    //Turns data on the canvas to JSON and stores it in the
    function storeCanvasToInput(canvas) {
        var annotationData = canvas.toJSON(['lockMovementX', 'lockMovementY', 'lockRotation', 'lockScalingX', 'lockScalingY', 'lockUniScaling', 'hasRotatingPoint']);
        $('#image_annotation_json').val(JSON.stringify(annotationData, undefined, 4));

        var originalSize = [imageToAnnotate.width(), imageToAnnotate.height()];
        if ($('#wcia-original-size').val().length <= 0) {
            $('#wcia-original-size').val(JSON.stringify(originalSize));
        }

    }

    //Only run when images area all loaded in case of slow connections or large images
    $('#canvas-area').imagesLoaded(function () {
        //Tolotra custom
        var array_positions = [];
        var object_positions = {};

        var add_annotation_obj_id_number = 0;
        var initial_object_data;

        var workspace_container = $('#wcia-preview-image');
        var my_resizable_params = {

            containment: "parent",
            stop: function (event, ui) {
                var widthPercentage = $(this).width() / imageToAnnotate.width();
                var heightPercentage = $(this).height() / imageToAnnotate.height();

                var object_id = $(this).data('id');
                var lookup_index_in_array_positions = array_positions.map(function (e) {
                    return e.id;
                }).indexOf(object_id);
                array_positions[lookup_index_in_array_positions].height = heightPercentage;
                array_positions[lookup_index_in_array_positions].width = widthPercentage;

                tolotra_display_object_json_in_textarea();

            }
        };

        //used by both loading data when already exists and new added element
        function tolotra_load_order(element, object_to_load) {
            if (typeof (object_to_load) != 'undefined') {
                element.find('input').val(object_to_load.order);
            }
        }

        function my_click_listener() {
            if (removeButton.hasClass('active')) {
                //if (confirm('Are you sure you want to remove this thing?')) {
                var object_id = $(this).data('id');
                var lookup_index_in_array_positions = array_positions.map(function (e) {
                    return e.id;
                }).indexOf(object_id);
                array_positions.splice(lookup_index_in_array_positions, 1);
                tolotra_display_object_json_in_textarea();
                $(this).remove();
                // } else {
                // Do nothing!
                // }
            }
        }

        function createRemoveCursor() {

            var canvas = document.createElement("canvas");
            canvas.width = 24;
            canvas.height = 24;
            //document.body.appendChild(canvas);
            var ctx = canvas.getContext("2d");
            ctx.fillStyle = "#ffffff";
            ctx.font = "22px FontAwesome";
            ctx.strokeStyle = "black";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText("\uf014", 12, 12);

            return canvas.toDataURL('image/png');
        }

        var dataUrl_remove_cursor = createRemoveCursor();
       //console.log(dataUrl_remove_cursor);

        function my_hover_listener(element) {
            element.on('mouseenter', function () {
                if (removeButton.hasClass('active')) {
                    element.css('cursor', 'url(' + dataUrl_remove_cursor + '), auto');
                } else {
                    if (element.is("input")) {
                        element.css('cursor', 'auto');
                    } else {
                        element.css('cursor', 'move');
                    }
                }
            });

            element.on('mouseleave', function () {
                //  element.css('cursor', 'auto');
            });

        }

        function my_input_change_listener() {

            var object_id = $(this).parent().data('id');
            var object_val = $(this).val();
            var lookup_index_in_array_positions = array_positions.map(function (e) {
                return e.id;
            }).indexOf(object_id);
            array_positions[lookup_index_in_array_positions].order = object_val;
            tolotra_display_object_json_in_textarea();
        }

        function tolotra_display_object_json_in_textarea() {

            $("#image_annotation_json_object").val(JSON.stringify(object_positions, undefined, 4));
        }

        var my_draggable_params = {
            containment: "parent", cursor: "move",
            // Find position where image is dropped.
            stop: function (event, ui) {

                // Show dropped position.
                var Stoppos = $(this).position();
                var leftPercentage = Stoppos.left / imageToAnnotate.width();
                var topPercentage = Stoppos.top / imageToAnnotate.height();
                var object_id = $(this).data('id');

                //searching the index of the dragged object
                var lookup_index_in_array_positions = array_positions.map(function (e) {
                    return e.id;
                }).indexOf(object_id);
                array_positions[lookup_index_in_array_positions].top = topPercentage;
                array_positions[lookup_index_in_array_positions].left = leftPercentage;

                tolotra_display_object_json_in_textarea();
            }
        };
        if (typeof  addAnnotationButton !== 'undefined') {
            addAnnotationButton.on('click', function () {
                toolButton.removeClass('active');
                $(this).addClass('active');
            });
        }

        function tolotra_new_makerElement(x_pos, y_pos) {

            var element = createMarkerElement(); //create new from scracth
            var element_marker_container = $(element);


            //initialize object newly created
            var the_object = {
                id: add_annotation_obj_id_number++,
                top: y_pos,
                left: x_pos,
                width: 20 / imageToAnnotate.width(), //this returns 20px
                height: 20 / imageToAnnotate.height(), //this returns 20px
                order: "0",
            };

            array_positions.push(the_object);
            if (!(object_positions)) {
                object_positions = {
                    "objects": array_positions,
                }
            }
            object_positions.last_unique_id = add_annotation_obj_id_number;
            element_marker_container = tolotra_set_style(element_marker_container, the_object); //new element
            tolotra_append_toScreen(element_marker_container);
            tolotra_set_listeners(element_marker_container);
            tolotra_display_object_json_in_textarea();
        }


        function loadInitialObjects() {
            // initialized from class-wcia_admin.php

            if (typeof currentWIPAObjectData[0] !== 'undefined') {
                initial_object_data = (currentWIPAObjectData[0]);
                return initial_object_data;
            } else {
               //console.log('script stopped, currentWIPAObjectData[1] undefined')
            }
        }

        //create a div outside and an input inside, used from both create and load element
        function createMarkerElement() {
            var a = document.createElement('div');
            a.setAttribute("id", "draggable4");
            a.setAttribute("data-id", add_annotation_obj_id_number);
            a.setAttribute("class", "img-annotator-marker-url");

            var number = document.createElement('input');
            number.innerHTML = add_annotation_obj_id_number;
            number.setAttribute("class", "order_indicator");
            number.setAttribute("required", "true");
            number.setAttribute("maxlength", "1");
            number.setAttribute("placeholder", "0");
            number.setAttribute("oninput", "javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);");
            number.setAttribute("type", "number");

            a.appendChild(number);
            return a;
        };
        //tolotra custom

        function tolotra_set_listeners(jq_element) {
            var jq_element = $(jq_element);
            jq_element.draggable(my_draggable_params);
            jq_element.resizable(my_resizable_params);
            jq_element.on('click', my_click_listener);
            my_hover_listener(jq_element); //
            my_hover_listener(jq_element.find("input"));


            jq_element.find('input').on('input', my_input_change_listener);
        }

        function tolotra_append_toScreen(element) {

            var parent = $('.wcia-canvas-area');
            parent.append(element);
        }

        function setUp_MouseHover_WorkSpace() {

            workspace_container.on('mouseenter', function () {
                if (addAnnotationButton.hasClass('active')) {
                    workspace_container.css('cursor', 'copy');
                }
            });
            workspace_container.on('mouseleave', function () {
                if (!addAnnotationButton.hasClass('active')) {
                    workspace_container.css('cursor', 'auto');
                }
                //  workspace_container.css('cursor', 'cell');
            });
            workspace_container.on('click', function (e) {
                var offset = $(this).offset();
                var x_percentage_pos = (e.pageX - offset.left) / imageToAnnotate.width();
                var y_percentage_pos = (e.pageY - offset.top) / imageToAnnotate.height();
                tolotra_new_makerElement(x_percentage_pos, y_percentage_pos);
            })
        }

        setUp_MouseHover_WorkSpace();

        //end tolotra custom


        //Goes through and deletes objects according to their ID
        function deleteItemsByID(id, canvas) {
            canvas.forEachObject(function (obj) {
                if ((obj.id === id)) {
                    canvas.remove(obj);
                }
            });
        }

        function tolotra_anotator_remove() {
            if (removeButton.hasClass('active')) {
                toolButton.removeClass('active');
            } else {
                toolButton.removeClass('active');
                removeButton.addClass('active')
            }
        }

        //removeButton.on('click', image_anotator_remove());
        removeButton.on('click', tolotra_anotator_remove);

        function handleToolButton() {
            toolButton.removeClass('active');
            $(this).addClass('active');
        }

        //Handle tool buttons
        // toolButton.on('click', handleToolButton());



        //Upload button functions
        $('#upload_image_button').click(function () {
            formfield = $('#upload_image').attr('name');
            tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
            return false;
        });

        //Opens the WordPress editor when the upload button is clicked and sets preview image
        window.send_to_editor = function (html) {

            var div = document.createElement('div');
            div.innerHTML = html;
            var firstImage = div.getElementsByTagName('img')[0];
            var imgSrc = firstImage ? firstImage.src : "";

            $('#upload_image').val(imgSrc);
            tb_remove();

            $('#wcia-preview-image').attr('src', imgSrc);
            window.setTimeout(function () {
                // resizeCanvas(fabricCanvas);
            }, 1000);

        };

        //Handles resizing the canvas and elements when picture size changes
        $(window).on('resize', function () {
            // resizeCanvas(fabricCanvas);
        });

        //Handles the shift key being pressed
        $(document).on('keyup keydown', function (e) {
            shifted = e.shiftKey
        });

        //After a load from JSON a polygon will show without an id, it needs to be removed if things are moved
        function removePolygonsWithoutIDs(canvas) {
            var allObjects = canvas.getObjects('polygon');
            var correctObject;

            canvas.forEachObject(function (obj) {
                if (obj.type === 'polygon' && typeof obj.id === 'undefined') {
                    canvas.remove(obj);
                }
            });
        }


        //Handles deletion
        $('html').keyup(function (e) {
            if (e.keyCode == 46) {
                //removeButton.click();
            }
        });

        //tolotra Custom

        function tolotra_set_style(a, object_to_load) {

            a = $(a);
            //loading from initial if already having data, else skip it, because it's new
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

        function tolotra_draw_loaded_data() {
            object_positions = loadInitialObjects();
            if (typeof (object_positions) != 'undefined') {
                add_annotation_obj_id_number = object_positions.last_unique_id;
                array_positions = object_positions.objects;
               //console.log(object_positions);
               //console.log(add_annotation_obj_id_number);
                for (var the_object_id in array_positions) {
//for in loop inside array returns integer refering to positions, that's why we used hasOwnProprety, the_object is int type
                    if (array_positions.hasOwnProperty(the_object_id)) {
                       //console.log(the_object_id, "outer function");
                        var a = createMarkerElement(); //create new and then style it according to data loaded
                        a = tolotra_set_style(a, array_positions[the_object_id]);
                        tolotra_append_toScreen(a); //loaded elements
                        tolotra_load_order(a, array_positions[the_object_id]);
                        tolotra_set_listeners(a);
                    }
                }
            } else {
                array_positions = [];
            }
        }

        if(edit_mode){
            tolotra_draw_loaded_data();
        }


    }); //end of image loaded


});
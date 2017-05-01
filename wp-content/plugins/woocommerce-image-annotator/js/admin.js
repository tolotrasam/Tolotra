(function( $ ) {
    $(function() {
         
        $( '.wcia-color-picker' ).wpColorPicker();
         
    });

	$('.button_icon').change(function (){
		
		alert('Enable View Annotation Icon on pro versions only..!')
		$(this).attr('checked',false);
	});

})( jQuery );
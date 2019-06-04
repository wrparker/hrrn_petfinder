jQuery( document ).ready(function() {
  var maxHeight = 0;

  jQuery(".petfinder-container").each(function(){
     if (jQuery(this).height() > maxHeight) { maxHeight = jQuery(this).height(); }
  });
  jQuery(".petfinder-container").height(maxHeight);

  jQuery("#name-filter").on('input', function(e){
      var search = jQuery(this).val();

      jQuery('.petfinder-rabbit-name').each(function(){
        if (jQuery(this).html().search(new RegExp(search, 'i')) === -1){
          jQuery(this).parent().closest('div').hide();
        }
        else{
          jQuery(this).parent().closest('div').show();
        }
      })
  });

});

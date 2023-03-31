jQuery( document ).ready(function() {
  var maxHeight = 0;

  jQuery(".petfinder-container").each(function(){
     if (jQuery(this).height() > maxHeight) { maxHeight = jQuery(this).height(); }
  });
  jQuery(".petfinder-container").height(maxHeight);

  // Name Filter
  jQuery("#name-filter").on('input', function(e){
    applyFilters();
  });

  jQuery("#gender-filter").on('change', function(e){
      applyFilters();
  });

    jQuery("#breed-filter").on('change', function(e){
        applyFilters();
    });

    jQuery("#age-filter").on('change', function(e){
        applyFilters();
    });

    jQuery("#size-filter").on('change', function(e){
        applyFilters();
    });

    jQuery('#reset-filters').on('click', function(e){
        resetFilters();
    });
});

function resetFilters(){
    jQuery("#name-filter").val('');
    jQuery("#age-filter").val('');
    jQuery("#breed-filter").val('');
    jQuery("#size-filter").val('');
    jQuery("#gender-filter").val('');

    jQuery('.petfinder-container').each(function() {
        jQuery(this).show();
    });

}

function applyFilters(){
    var name = jQuery("#name-filter").val().trim();
    var age = jQuery("#age-filter").val();
    var breed = jQuery("#breed-filter").val();
    var size = jQuery("#size-filter").val();
    var gender = jQuery("#gender-filter").val();


    jQuery('.petfinder-container').each(function() {
        var shown = true;

        //Name filter
        if (jQuery(this).find('.petfinder-rabbit-name').html().search(new RegExp(name, 'i')) === -1){
            shown = false;
        }

        var breed_size_age = jQuery(this).find('.petfinder-breed-size-age').html();
        // Breed filter
        if (breed_size_age.search(new RegExp(breed, 'i')) === -1){
            shown = false;
        }

        // age filter
        if(breed_size_age.search(new RegExp(age, 'i')) === -1){
            shown = false
        }

        //size filter
        if(breed_size_age.search(new RegExp(size, 'i')) === -1){
            shown = false;
        }

        //gender filter
        if(gender!= '' && jQuery(this).find('.female').length && gender=='Male'){
            shown = false;
        }
        if(gender != '' && jQuery(this).find('.male').length && gender=='Female'){
            shown = false;
        }

        if(shown){
            jQuery(this).show();
        }
        else{
            jQuery(this).hide();
        }

    });
}

jQuery( document ).ready(function() {

    jQuery(this).tooltip();
    jQuery(".vaccination-legend").tooltip({content:"<h1>Vaccination List:</h1><ul><li>RHDV</li><li>Pasteurella</li></ul>"});


    jQuery(".vaccination-legend").click(function(event) {
        event.preventDefault();
        console.log("Link clicked but default behavior prevented."); 
    });
});

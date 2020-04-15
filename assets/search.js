  jQuery(document).ready(function ($) {

     $('#smos_submit').on('click', function (e) {
        e.preventDefault();

        var ordnolist = $('#smos_order_numbers_input').val();

        if (ordnolist == '') {
           window.alert('Please provide a list of order numbers to search for!');
        }
        else {
           var data = {
              'action':'search_results',
              'order_nos': ordnolist
           };

           $.post(ajaxurl, data, function(response){
              $('#smos_results_actual').html(response);
           });
        }
     });
  });
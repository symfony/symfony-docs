<script>
var $sport = $('#meetup_sport');
// When sport gets selected ...
$sport.change(function(){
  // ... retrieve the corresponding form.
  var $form = $(this).closest('form');
  // Simulate form data, but only include the selected sport value.
  var data = {};
  data[$sport.attr('name')] = $sport.val();
  // Submit data via AJAX to the form's action path.
  $.ajax({
    url : $form.attr('action'),
    type: $form.attr('method'),
    data : data,
    success: function(html) {
      // Replace current position field ...
      $('#meetup_position').replaceWith(
        // ... with the returned one from the AJAX response.
        $(html).find('#meetup_position')
      );
      // Position field now displays the appropriate positions.
    }
  });
});
</script>
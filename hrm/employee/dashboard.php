<?php 
global $wpdb, $current_user;

?>

<div class="wrap">
    <h2>Welcome <?php echo $current_user->display_name; ?>!</h2>
</div>


<div class="punch-in-widget">

  <div class="widget-header">
    <?php echo date(get_option('date_format')); ?>
    <div class="digital-clock">00:00:00</div>
  </div> 
  <div class="widget-body">
    <button class="punch-in-btn">Punch in</button>
    <button>Punch out</button>
  </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function($) {
      clockUpdate();
      setInterval(clockUpdate, 1000);

      $('.punch-in-btn').on('click', function(){

        $.ajax({
          url: ajax.url,
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'user_punch_in',
          },
          success: function (result) {
            console.log(result);
          },
          error: function (error) {
            console.log(error);
          }
        });

      });


    })

    function clockUpdate() {
        var date = new Date();

        function addZero(x) {
            if (x < 10) {
              return x = '0' + x;
            } else {
              return x;
            }
        }

        function twelveHour(x) {
            if (x > 12) {
              return x = x - 12;
            } else if (x == 0) {
              return x = 12;
            } else {
              return x;
            }
        }

        var h = addZero(twelveHour(date.getHours()));
        var m = addZero(date.getMinutes());
        var s = addZero(date.getSeconds());

        jQuery('.digital-clock').text(h + ':' + m + ':' + s);
    }

</script>
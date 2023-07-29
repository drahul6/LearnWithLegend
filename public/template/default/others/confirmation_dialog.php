<script>
  function ConfirmationDialog($request, $message = null) {
    this.$request = $request;

    if ($message == null) {
      $("#dialog_message").html("Are you sure You want to continue?");

    } else {

      $("#dialog_message").html($message);
    }


    this.open_dialog = function() {
      $('#confirmation_dialog').css('display', 'block');
      $('#confirmation_dialog').modal('show');
    }

    this.open_dialog();

    this.confirm = function() {
      window.location.href = this.$request;
    }

  }

  function test(argument) {
    // body...
  }



  function DialogJS($function, $parameters, $message = null, $object = null) {

    this.$function = $function;
    this.$object = $object;



    if ($message == null) {
      $("#dialog_message").html("Are you sure You want to continue?");

    } else {

      $("#dialog_message").html($message);
    }




    this.open_dialog = function() {
      $('#confirmation_dialog').modal('show');
    }


    this.open_dialog();

    this.confirm = function() {
      if (this.$object == null) {
        this.$function.apply(this, $parameters);
      } else {
        this.$function.apply(this.$object, $parameters);
      }
      this.hide_modal();
    }

    this.hide_modal = function() {
      $('#confirmation_dialog').modal('hide');
    }



  }
</script>


<script src="<?= asset; ?>/angulars/ajax-form.js"></script>

<!-- Modal -->
<div id="confirmation_dialog" style="display: none;" class="modal fade" role="dialog">
  <div class="modal-dialog" style="display: block;">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" style="color: black;">Confirmation </h4>
      </div>
      <center class="modal-body">
        <h4 id="dialog_message" style="color: black;">Are you sure You want to continue? </h4>
      </center>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary" data-dismiss="modal">
          No
        </button>
        <button type="button" class="btn btn-primary" onclick="$confirm_dialog.confirm();">
          Yes
        </button>
      </div>
    </div>

  </div>
</div>
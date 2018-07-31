
<div class="row">
  <div class="col-md-12">
    <div class="form-group">
        <input id="file_area" type="file" name="files[]" multiple>
               <!--<input id="temp_file" type="file" name="file"
                      data-allowed-file-extensions='["jpeg", "jpg"]'>-->

    </div>
  </div>

</div>

<div class="form-group">
    <hr>
    <a href="/artworks/store/step2" class="btn btn-primary pull-right btn-block">
      @lang('forms.next')
    </a>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
  jQuery("a.pull-right").attr("disabled", true);
  jQuery("a.pull-right").attr("href", "#");
  var file_input = $("#file_area");
  file_input.fileinput({
      uploadExtraData: {
        '_token': '{{ csrf_token() }}'
      },
      uploadUrl: "/artworks/store", // server upload action
      uploadAsync: false,
      allowedFileExtensions: ['jpg', 'png', 'gif'],
      maxFileCount: 10,
      maxFileSize: 5120,
      minImageWidth: 800,
      minImageHeight: 600,
      maxImageWidth: 1900,
      maxImageHeight: 1200,
      showUpload: true,
      browseOnZoneClick: true,
      overwriteInitial: false,
  }).on('filebatchpreupload', function(event, data) {
        var n = data.files.length, files = n > 1 ? n + ' files' : 'one file';
        if (!window.confirm("Are you sure you want to upload " + files + "?")) {
            return {
                message: "Upload aborted!", // upload error message
                data:{} // any other data to send that can be referred in `filecustomerror`
            };
        }
        jQuery("a.pull-right").attr("disabled", true);
        jQuery("a.pull-right").attr("href", "#");
  }).on('filebatchuploadsuccess', function(event, data, previewId, index) {
    jQuery("a.pull-right").attr("disabled", false);
    jQuery("a.pull-right").attr("href", "/artworks/store/step2");
  });
});

</script>

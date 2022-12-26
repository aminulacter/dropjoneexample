<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('File Upload') }}
        </h2>
    </x-slot>
    <?php
    $ts = time();
    $user_id = Auth::user()->id;
    $date = date("Y-m-d");
    ?>
    <div class="container-fluid">
        <form id="uploadform" class="dropzone" action="{{ route('file-upload') }}">
            <!-- this is were the previews should be shown. -->
            @csrf
         <!-- Now setup your input fields -->
            <input type="file" name="file" >
            <input type="hidden" name="dataTS" id="dataTS" value="{{ $ts }}">
            <input type="hidden" name="dataDATE" id="dataDATE" value="{{ $date }}">
            <button type="submit">Submit data and files!</button>
        </form>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="col-md-12 mb-4">
                    <button type="button" class="btn btn-primary btn-ico" data-toggle="modal" data-target="#uploaderModal"><i class="fa fa-files-o"></i> {{ __('File Upload') }}</button>
                </div>
            </div>
        </div>
    </div>
    <x-slot name="css">
        <link href="{{ asset('vendor/dropzone/dropzone.min.css') }}" rel="stylesheet">
    </x-slot>
    <x-slot name="script">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
        <script src="{{ asset('vendor/dropzone/dropzone.min.js') }}"></script>


        <script>
            var home_url = "{{env('APP_URL') }}";
            var deleteAction = '{{ route("file-delete") }}';
           var generalTS =  document.getElementById('dataTS').value;
           var generalDATE = document.getElementById('dataDATE').value;
            var token = '{!! csrf_token() !!}';

            Dropzone.options.uploadform =
                {
                    init: function() {
                        this.on("addedfile", file => {
                            console.log("A file has been added");
                        });
                    },
                    parallelUploads: 1,  // since we're using a global 'currentFile', we could have issues if parallelUploads > 1, so we'll make it = 1
                    maxFilesize: 1024,   // max individual file size 1024 MB
                    maxFiles: 1,
                    createImageThumbnails: false,
                    dictDefaultMessage: "Upload Lesson File",
                    uploadMultiple: false,
                    disablePreviews: false,
                    chunking: true,      // enable chunking
                    forceChunking: true, // forces chunking when file.size < chunkSize
                    parallelChunkUploads: true, // allows chunks to be uploaded in parallel (this is independent of the parallelUploads option)
                    chunkSize: 2000000,  // chunk size 2,000,000 bytes (~2MB)
                    retryChunks: true,   // retry chunks on failure
                    retryChunksLimit: 3, // retry maximum of 3 times (default is 3)
                    renameFile: function(file) {
                        var dt = new Date();
                        var time = dt.getTime();
                        return time+"_"+file.name;
                    },
                    acceptedFiles: ".jpeg,.jpg,.png,.txt,.mp4,.mkv",
                    addRemoveLinks: true,
                    timeout: 50000,
                     removedfile: function(file) {
                        var name = file.upload.filename;
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            type: 'POST',
                            url: deleteAction,
                            data: {
                                filename: name,
                                ts: generalTS,
                                date: generalDATE,
                            },
                            success: function (data){
                                console.log("File has been successfully removed!!");
                            },
                            error: function(e) {
                                console.log(e);
                            }});
                        var fileRef;
                        return (fileRef = file.previewElement) != null ?
                            fileRef.parentNode.removeChild(file.previewElement) : void 0;
                    },

                    success: function(file, response)
                    {
                        console.log(response);
                        this.disablePreviews = true
                    },
                    error: function(file, response)
                    {
                        return false;
                    }
                };



        </script>

        {{--        <script src="{{ asset('js/file_upload.js') }}" defer></script>--}}
    </x-slot>
</x-app-layout>

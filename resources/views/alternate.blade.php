<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
{{--    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>--}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>


{{--    <script src="https://unpkg.com/dropzone@5/dist/dropzone.js"></script>--}}
    <link href="{{ asset('vendor/dropzone/dropzone.min.css') }}" rel="stylesheet">
    <script src="{{ asset('vendor/dropzone/dropzone.min.js') }}"></script>

    <script>
        Dropzone.autoDiscover = false
    </script>

    <style>
        html,
        body {
            height: 100%;
        }
        #actions {
            margin: 2em 0;
        }
        .progress {
            width: 10rem;
        }

        /* Mimic table appearance */
        div.table {
            display: table;
        }
        div.table .file-row {
            display: table-row;
        }
        div.table .file-row > div {
            display: table-cell;
            vertical-align: top;
            border-top: 1px solid #ddd;
            padding: 8px;
        }
        div.table .file-row:nth-child(odd) {
            background: #f9f9f9;
        }

        /* The total progress gets shown by event listeners */
        #total-progress {
            opacity: 0;
            transition: opacity 0.3s linear;
        }

        /* Hide the progress bar when finished */
        #previews .file-row.dz-success .progress {
            opacity: 0;
            transition: opacity 0.3s linear;
        }

        /* Hide the delete button initially */
        #previews .file-row .delete {
            display: none;
        }

        /* Hide the start and cancel buttons and show the delete button */

        #previews .file-row.dz-success .start,
        #previews .file-row.dz-success .cancel {
            display: none;
        }
        #previews .file-row.dz-success .delete {
            display: block;
        }
    </style>
</head>
<body>
    <?php
    $ts = time();
    $user_id = Auth::user()->id;
    $date = date("Y-m-d");
    ?>
    <div class="container" id="container">
        <div id="actions" class="row">
            <div class="col-lg-7">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button">

            <span>Add files...</span>
          </span>
                <button type="submit" class="btn btn-primary start">

                    <span>Start upload</span>
                </button>
            </div>

            <div class="col-lg-5">

            </div>
        </div>

        <div class="table table-striped">
            <div id="template" class="file-row">

                <div>
                    <span class="preview"><img data-dz-thumbnail /></span>
                </div>
                <div>
                    <p class="name" data-dz-name></p>
                    <strong class="error text-danger" data-dz-errormessage></strong>
                </div>
                <div>
                    <p class="size" data-dz-size></p>
                    <div
                        id="total-progress"
                        class="progress active"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="0"
                    >
                        <div
                            class="progress-bar progress-bar-striped progress-bar-animated progress-bar-success"
                            style="width: 0%"
                            role="progressbar"
                            data-dz-uploadprogress
                        ></div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary start">
                        <i class="glyphicon glyphicon-upload"></i>
                        <span>Start</span>
                    </button>
                    <button type="button" data-dz-remove class="btn btn-warning cancel">
                        <i class="glyphicon glyphicon-ban-circle"></i>
                        <span>Cancel</span>
                    </button>
                    <button type="button" data-dz-remove class="btn btn-danger delete">
                        <i class="glyphicon glyphicon-trash"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
            <div id="previews"></div>
        </div>
    </div>
    <div>
        <input type="hidden" value="" id="file_keeper">
    </div>
        <script>
            // Get the template HTML and remove it from the doument
            var deleteAction = '{{ route("file-delete") }}';
            var generalTS = {{ $ts }};
            var generalDATE = {{ $date }};
            var token = '{!! csrf_token() !!}';
            var previewNode = document.querySelector('#template')
            previewNode.id = ''
            var previewTemplate = previewNode.parentNode.innerHTML
            previewNode.parentNode.removeChild(previewNode)

            var myDropzone = new Dropzone(document.getElementById('container'), {
                // Make the whole body a dropzone
                url: '{{ route('file-upload') }}', // Set the url
                method: "post",
                thumbnailWidth: 80,
                thumbnailHeight: 80,
                previewTemplate: previewTemplate,
                parallelUploads: 1,  // since we're using a global 'currentFile', we could have issues if parallelUploads > 1, so we'll make it = 1
                maxFilesize: 1024,   // max individual file size 1024 MB
                maxFiles: 1,
                chunking: true,      // enable chunking
                forceChunking: true, // forces chunking when file.size < chunkSize
                parallelChunkUploads: true, // allows chunks to be uploaded in parallel (this is independent of the parallelUploads option)
                chunkSize: 2000000,  // chunk size 2,000,000 bytes (~2MB)
                retryChunks: true,   // retry chunks on failure
                retryChunksLimit: 3,
                retryChunksLimit: 3, // retry maximum of 3 times (default is 3)
                renameFile: function(file) {
                    var dt = new Date();
                    var time = dt.getTime();
                    return time+"_"+file.name;
                },
                acceptedFiles: ".jpeg,.jpg,.png,.txt,.mp4,.mkv",
                autoQueue: false, // Make sure the files aren't queued until manually added
                previewsContainer: '#previews', // Define the container to display the previews
                clickable: '.fileinput-button', // Define the element that should be used as click trigger to select files.
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
                            document.getElementById('file_keeper').value = ''
                        },
                        error: function(e) {
                            console.log(e);
                        }});
                    var fileRef;
                    return (fileRef = file.previewElement) != null ?
                        fileRef.parentNode.removeChild(file.previewElement) : void 0;
                },
            })

            myDropzone.on('addedfile', function (file) {
                // Hookup the start button
               file.previewElement.querySelector('.start').onclick = function () {
                    myDropzone.enqueueFile(file)
                }
            })

            // Update the total progress bar
            // myDropzone.on('totaluploadprogress', function (progress) {
            //     // document.querySelector('#total-progress .progress-bar').style.width =
            //     //     progress + '%'
            // })

            myDropzone.on('sending', function (file, xhr, formData) {

                formData.append("_token", "{{ csrf_token() }}");
                // Show the total progress bar when upload starts
                document.querySelector('#total-progress').style.opacity = '1'
                // And disable the start button
                file.previewElement
                    .querySelector('.start')
                    .setAttribute('disabled', 'disabled')
            })

            // Hide the total progress bar when nothing's uploading anymore
            myDropzone.on('queuecomplete', function (progress) {
                document.querySelector('#total-progress').style.opacity = '0'
            })

            // Setup the buttons for all transfers
            // The "add files" button doesn't need to be setup because the config
            // `clickable` has already been specified.
            document.querySelector('#actions .start').onclick = function () {
                myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
            }
            // document.querySelector('#actions .cancel').onclick = function () {
            //     myDropzone.removeAllFiles(true)
            // }

            // Now fake the file upload, since GitHub does not handle file uploads
            // and returns a 404

            myDropzone.on('success', function (file, response) {
                console.log(response)
                    const file_name = response.path + response.name
                document.getElementById('file_keeper').value = file_name
            })

        </script>



    </body>
</html>

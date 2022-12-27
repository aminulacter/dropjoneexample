<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />

    <!-- Latest compiled and minified CSS -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css"
    />

    <script src="https://unpkg.com/dropzone@5/dist/dropzone.js"></script>
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
<div class="container" id="container">
       <div id="actions" class="row">
        <div class="col-lg-7">
            <!-- The fileinput-button span is used to style the file input field as button -->
            <span class="btn btn-success fileinput-button">
            <i class="glyphicon glyphicon-plus"></i>
            <span>Add files...</span>
          </span>
            <button type="submit" class="btn btn-primary start">
                <i class="glyphicon glyphicon-upload"></i>
                <span>Start upload</span>
            </button>
            <button type="reset" class="btn btn-warning cancel">
                <i class="glyphicon glyphicon-ban-circle"></i>
                <span>Cancel upload</span>
            </button>
        </div>

        <div class="col-lg-5">

        </div>
    </div>

    <div class="table table-striped">
        <div id="template" class="file-row">
            <!-- This is used as the file preview template -->
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
                    <span>Starts</span>
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

    <script>
        // Get the template HTML and remove it from the doument
        var previewNode = document.querySelector('#template')
        previewNode.id = ''
        var previewTemplate = previewNode.parentNode.innerHTML
        previewNode.parentNode.removeChild(previewNode)

        var myDropzone = new Dropzone(document.body, {
            // Make the whole body a dropzone
            url: '{{ route('file-upload') }}', // Set the url
            method: "post",
            thumbnailWidth: 80,
            thumbnailHeight: 80,
            previewTemplate: previewTemplate,
            parallelUploads: 1,  // since we're using a global 'currentFile', we could have issues if parallelUploads > 1, so we'll make it = 1
            maxFilesize: 1024,   // max individual file size 1024 MB
            maxFiles: 1,

            autoQueue: false, // Make sure the files aren't queued until manually added
            previewsContainer: '#previews', // Define the container to display the previews
            clickable: '.fileinput-button', // Define the element that should be used as click trigger to select files.
        })

        myDropzone.on('addedfile', function (file) {
            // Hookup the start button
            file.previewElement.querySelector('.start').onclick = function () {
                myDropzone.enqueueFile(file)
            }
        })

        // Update the total progress bar
        myDropzone.on('totaluploadprogress', function (progress) {
            document.querySelector('#total-progress .progress-bar').style.width =
                progress + '%'
        })

        myDropzone.on('sending', function (file) {
            {{--formData.append("_token", "{{ csrf_token() }}");--}}
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
        document.querySelector('#actions .cancel').onclick = function () {
            myDropzone.removeAllFiles(true)
        }

        // Now fake the file upload, since GitHub does not handle file uploads
        // and returns a 404

        var minSteps = 6,
            maxSteps = 600,
            timeBetweenSteps = 100,
            bytesPerStep = 100000

        myDropzone.uploadFiles = function (files) {
            var self = this

            for (var i = 0; i < files.length; i++) {
                var file = files[i]
                totalSteps = Math.round(
                    Math.min(maxSteps, Math.max(minSteps, file.size / bytesPerStep))
                )
                self.emit('sending', file)
                for (var step = 0; step < totalSteps; step++) {
                    var duration = timeBetweenSteps * (step + 1)
                    setTimeout(
                        (function (file, totalSteps, step) {
                            return function () {
                                file.upload = {
                                    progress: (100 * (step + 1)) / totalSteps,
                                    total: file.size,
                                    bytesSent: ((step + 1) * file.size) / totalSteps,
                                }

                                self.emit(
                                    'uploadprogress',
                                    file,
                                    file.upload.progress,
                                    file.upload.bytesSent
                                )
                                if (file.upload.progress == 100) {
                                    file.status = Dropzone.SUCCESS
                                    self.emit('success', file, 'success', null)
                                    self.emit('complete', file)
                                    self.processQueue()
                                }
                            }
                        })(file, totalSteps, step),
                        duration
                    )
                }
            }
        }
    </script>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.title-meta', ['subTitle' => 'Problem List | T-QIMS'])
    @include('layouts.head-css')
    <link href="{{ asset('assets/vendor/dropzone/dropzone.css') }}" rel="stylesheet" type="text/css" />
    <style>
        /* Custom Dropzone Preview Styles */
        #dropzone-preview .card {
            margin-bottom: 0.5rem;
            border: 1px solid #e9ebec;
            box-shadow: none;
        }

        #dropzone-preview .card-body {
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        #dropzone-preview .file-details {
            display: flex;
            align-items: center;
            flex-grow: 1;
        }

        #dropzone-preview .dz-image {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0;
            background: #eee;
        }

        #dropzone-preview .dz-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #dropzone-preview .file-info h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        #dropzone-preview .file-info small {
            color: #6c757d;
        }

        #dropzone-preview .dz-remove {
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        @include('layouts.main-nav')
        @include('layouts.topbar')

        <div class="page-container">
            <div class="page-content">
                @include('layouts.page-title', [
                    'title' => 'Problem Management',
                    'subTitle' => 'Problem List',
                ])

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="btn-group" role="group" aria-label="View switch">
                            <button type="button" class="btn btn-soft-primary active" id="btnViewList">List
                                Problem</button>
                            <button type="button" class="btn btn-soft-primary" id="btnViewGallery">Gallery
                                Problem</button>
                        </div>
                        <div>
                            <button id="btnFilterTable" class="btn btn-info me-2"><i class="bi bi-funnel"></i> Filter
                                Table</button>
                            <button id="btnProblemAdd" class="btn btn-primary">Add Problem</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-end mb-3">

                        </div>
                        <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#manufacturing" role="tab"
                                    data-type="manufacturing">
                                    <span class="d-none d-sm-block">Manufacturing</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kentokai" role="tab"
                                    data-type="kentokai">
                                    <span class="d-none d-sm-block">Kentokai</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ks" role="tab" data-type="ks">
                                    <span class="d-none d-sm-block">KS</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kd" role="tab" data-type="kd">
                                    <span class="d-none d-sm-block">KD</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#sk" role="tab" data-type="sk">
                                    <span class="d-none d-sm-block">SK</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#buyoff" role="tab"
                                    data-type="buyoff">
                                    <span class="d-none d-sm-block">Buy Off</span>
                                </a>
                            </li>
                        </ul>
                        <div id="problemsTableContainer"></div>
                        <div id="problemsGalleryContainer" class="d-none"></div>
                    </div>
                </div>

                @include('admin.problem.modaladd')
                @include('admin.problem.modaldetail')
                @include('admin.problem.modalexport')
                
                <div class="modal fade" id="dispatchModal" tabindex="-1" aria-labelledby="dispatchModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="dispatchModalLabel">Send Email</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="dispatchForm" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="sendTo" class="form-label">Send To</label>
                                        <input type="email" class="form-control" id="sendTo" name="sendTo" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cc" class="form-label">CC</label>
                                        <input type="email" class="form-control" id="cc" name="cc">
                                    </div>
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Subject</label>
                                        <input type="text" class="form-control" id="subject" name="subject" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message</label>
                                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="attachment" class="form-label">Attachment</label>
                                        <input type="file" class="form-control" id="attachment" name="attachment">
                                    </div>
                                    <input type="hidden" name="problem_id" id="problem_id">
                                    <button type="submit" class="btn btn-primary">Send Email</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.vendor-scripts')
    <script src="{{ asset('assets/vendor/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/problems.js') }}"></script>
    <script src="{{ asset('assets/js/components/form-clipboard.js') }}"></script>
    <script>
        $(function() {
            function loadList() {
                $('#problemsTableContainer').load('/admin/problems/table', function() {
                    if (window.loadProblems) window.loadProblems();
                });
            }

            function loadGallery() {
                $('#problemsGalleryContainer').load('/admin/problems/gallery', function() {
                    if (window.loadProblemGallery) window.loadProblemGallery();
                });
            }
            loadList();

            $('#btnViewList').on('click', function() {
                $('#btnViewGallery').removeClass('active');
                $(this).addClass('active');
                $('#problemsGalleryContainer').addClass('d-none');
                $('#problemsTableContainer').removeClass('d-none');
                loadList();
            });
            $('#btnViewGallery').on('click', function() {
                $('#btnViewList').removeClass('active');
                $(this).addClass('active');
                $('#problemsTableContainer').addClass('d-none');
                $('#problemsGalleryContainer').removeClass('d-none');
                loadGallery();
            });
        });
    </script>
</body>

</html>

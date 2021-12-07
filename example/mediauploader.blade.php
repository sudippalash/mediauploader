<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Media Uploader</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-3">
            <form method="POST" action="{{ route('image-upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Image</span>
                    </div>

                    <input type="file" class="form-control" name="file" required>

                    <div class="input-group-append">
                        <span class="input-group-text"><button type="submit" class="btn-primary">Submit</button></span>
                    </div>
                </div>
            </form>
            
            <form method="POST" action="{{ route('webp-upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">WEBP</span>
                    </div>

                    <input type="file" class="form-control" name="file" required>

                    <div class="input-group-append">
                        <span class="input-group-text"><button type="submit" class="btn-primary">Submit</button></span>
                    </div>
                </div>
            </form>
            
            <form method="POST" action="{{ route('any-upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Any</span>
                    </div>

                    <input type="file" class="form-control" name="file" required>

                    <div class="input-group-append">
                        <span class="input-group-text"><button type="submit" class="btn-primary">Submit</button></span>
                    </div>
                </div>
            </form>
            
            <form method="POST" action="{{ route('url-upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">URL</span>
                    </div>

                    <input type="text" class="form-control" name="file" required>

                    <div class="input-group-append">
                        <span class="input-group-text"><button type="submit" class="btn-primary">Submit</button></span>
                    </div>
                </div>
            </form>
            
            <form method="POST" action="{{ route('thumb-upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Thumb</span>
                    </div>

                    <input type="text" class="form-control" name="file" required>

                    <div class="input-group-append">
                        <span class="input-group-text"><button type="submit" class="btn-primary">Submit</button></span>
                    </div>
                </div>
            </form>
            
            <form method="POST" action="{{ route('base-upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">BASE64</span>
                    </div>

                    <textarea class="form-control" name="file" required rows="6"></textarea>

                    <div class="input-group-append">
                        <span class="input-group-text"><button type="submit" class="btn-primary">Submit</button></span>
                    </div>
                </div>
            </form>
            
            <form method="POST" action="{{ route('content-upload') }}" enctype="multipart/form-data">
                @csrf

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Content</span>
                    </div>

                    <textarea class="form-control" name="file" required rows="6"></textarea>

                    <div class="input-group-append">
                        <span class="input-group-text"><button type="submit" class="btn-primary">Submit</button></span>
                    </div>
                </div>
            </form>
        </div>
    </body>

    {{-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script> --}}
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>PBF | Koperasi Menjangan Enam</title>
        <link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
        <script src="https://use.fontawesome.com/releases/v5.15.3/js/all.js" crossorigin="anonymous"></script>
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&amp;display=swap" rel="stylesheet" />
        <link href="{{ asset('resources/css/styles.css') }}" rel="stylesheet" />
    </head>

    <body style="background-image: url('img/bg_obat2.png'); opacity:0.9; background-size: cover">        
        <div class="masthead">
            <div class="masthead-content text-white">
                <div class="container-fluid px-3 px-lg-0">
                    <div class="d-flex align-items-center">
                        <img src="{{asset('img/logo_pbf.png')}}" style='width:170px; height:170px; margin-left: auto; margin-right: 20px'>
                        <div>
                            <h2 class="mt-4">Unit Usaha PBF</h2>
                            <div class="fst-italic lh-1 mb-2 mb-3"><h5> Koperasi Menjangan Enam</h5></div>
                        </div>
                    </div>
                    <form>
                        <div class="row input-group-newsletter">
                            <div class="col-auto" href="{{ route('login') }}" ><a href="{{ route('login') }}" class="btn btn-light" style="margin-top:30px; margin-left: 60%">Masuk</a></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="social-icons">
            <div class="d-flex flex-row flex-lg-column justify-content-center align-items-center h-100 mt-3 mt-lg-0">
            </div>
        </div>
        <div style="position:absolute; bottom: 0; right:0;color:rgb(0, 0, 0); padding-right:5px">
            <a>www.ciptasolutindo.id</a>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="{{ asset('resources/js/scripts.js') }}"></script>
        <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    </body>
</html>



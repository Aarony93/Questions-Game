
@switch(Route::current()->getName())
    @case('game')
        <link href="{{ asset('vendor/toastr/toastr.min.css') }}" rel="stylesheet">
    @break

@endswitch

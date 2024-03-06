
{{-- <script type="module" >

let toasrOptions = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-bottom-center",
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "50000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut",
};


// csrf token for all the ajax requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

</script> --}}

@switch(Route::current()->getName())
    @case('game')

        <script type="module" src="{{ asset('vendor/blockui/jquery.blockui.js') }}"></script>
        <script type="module" src="{{ asset('vendor/toastr/toastr.min.js') }}"></script>

        @include('partials.js.game')
    @break


@endswitch

{{--This is only included for backwards compatibility. It will be removed at a future stage.--}}
@if (config('webmonksblog.search.search_enabled') )
    @include('webmonksblog::sitewide.search_form')
@endif
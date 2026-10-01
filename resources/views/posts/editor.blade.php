{{-- HTML editor (TinyMCE) pre text článku aj popis kanála. Voliteľne
     @include('posts.editor', ['selector' => '#description', 'height' => 280]).

     Spúšťa sa až cez arReady (partials/ar-ready): Vue prekresľuje celý #app
     a editor pripojený skôr zahodí — ostal po ňom len holý textarea. --}}
@push('scripts')
    <script src="{{ asset('tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        window.arReady(() => tinymce.init({
            selector: @json($selector ?? '#editor'),
            language: 'sk',
            branding: false,
            menubar: false,
            height: @json($height ?? 300),
            plugins: 'lists link autolink paste',
            toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist blockquote | link unlink | removeformat',
            block_formats: 'Odsek=p; Nadpis 2=h2; Nadpis 3=h3; Nadpis 4=h4',
            link_title: false,
            target_list: false,
            default_link_target: '_blank',
        }));
    </script>
@endpush

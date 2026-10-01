{{-- App::environment(), nie env('APP_ENV') — po `php artisan config:cache`
     vracia env() null a analytika sa na produkcii ticho vypne. --}}
@production
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-18613776-13"></script>
<script nonce="{{ csp_nonce() }}">
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-18613776-13');
</script>


<script type="text/javascript" nonce="{{ csp_nonce() }}">
    window.smartlook||(function(d) {
        var o=smartlook=function(){ o.api.push(arguments)},h=d.getElementsByTagName('head')[0];
        var c=d.createElement('script');o.api=[];c.async=true;c.type='text/javascript';
        c.charset='utf-8';c.src='https://rec.smartlook.com/recorder.js';h.appendChild(c);
    })(document);
    smartlook('init', 'c82e728b86a628d4e1b22b14b3b0258dd413ecf0');
</script>
@endproduction
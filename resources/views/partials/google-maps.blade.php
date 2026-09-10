{{--
    Google Maps Platform bootstrap loader.

    Include ONCE per page that renders a map, inside the scripts stack:
        @include('partials.google-maps')

    Individual maps then pull only the libraries they need, e.g.
        const { Map } = await google.maps.importLibrary('maps');

    Requires GOOGLE_MAPS_API_KEY and GOOGLE_MAPS_MAP_ID in .env.
    The Map ID is mandatory for AdvancedMarkerElement.
--}}
<script>
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: @json(config('services.google_maps.key')),
    v: "weekly",
});

// Shared config consumed by assets/js/maps/*.js
window.APP_MAPS = {
    mapId: @json(config('services.google_maps.map_id')),
    defaultMapTypeId: 'hybrid',
};
</script>

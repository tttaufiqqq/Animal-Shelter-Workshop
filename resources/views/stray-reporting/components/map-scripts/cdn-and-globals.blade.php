<!-- Leaflet Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        onerror="window.LEAFLET_FAILED = true"></script>
<script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.min.js"></script>

<script>
    // =============================================
    // GPS LOCATION TRACKING - ENHANCED ACCURACY VERSION
    // =============================================

    // Global variables
    let map, marker, circle;
    let mapInitialized = false;
    let watchPositionId = null;
    let currentPosition = null;
    let isManualAdjustment = false;

    // Enhanced Malaysian state mapping
    const malaysiaStates = {
        'Johor': ['johor', 'johore', 'johor bahru', 'jb', 'j.b.', 'j.b'],
        'Kedah': ['kedah', 'alor setar', 'alor star'],
        'Kelantan': ['kelantan', 'kota bharu', 'kota bahru'],
        'Malacca': ['malacca', 'melaka', 'malaka'],
        'Negeri Sembilan': ['negeri sembilan', 'n.sembilan', 'n sembilan', 'seremban', 'n.s', 'n.s.'],
        'Pahang': ['pahang', 'kuantan', 'kuala lipis'],
        'Penang': ['penang', 'pulau pinang', 'georgetown', 'george town', 'penang island'],
        'Perak': ['perak', 'ipoh', 'taiping'],
        'Perlis': ['perlis', 'kangar'],
        'Sabah': ['sabah', 'kota kinabalu', 'kk', 'sandakan', 'tawau'],
        'Sarawak': ['sarawak', 'kuching', 'sibu', 'miri'],
        'Selangor': ['selangor', 'shah alam', 'petaling jaya', 'pj', 'subang jaya', 'klang'],
        'Terengganu': ['terengganu', 'kuala terengganu', 'k.terengganu'],
        'Kuala Lumpur': ['kuala lumpur', 'kl', 'k.l.', 'k.lumpur', 'wilayah persekutuan kuala lumpur'],
        'Putrajaya': ['putrajaya', 'putra jaya', 'wilayah persekutuan putrajaya'],
        'Labuan': ['labuan', 'w.p. labuan', 'wilayah persekutuan labuan']
    };
</script>

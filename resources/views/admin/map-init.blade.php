<div class="col-lg-12" id="map">
    <img src="/public/images/car-icon-DrivingClient-32.png">
    <img src="/public/images/car-icon-Open-32.png">
    <img src="/public/images/car-icon-Arrived-32.png">
    <img src="/public/images/car-icon-Accepted-32.png">
</div>
<script src="{{ asset('js/onlineMapCars.js') }}"></script>
<script>
    var map;
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 53.907460714169574, lng: 27.553926315407600},
            zoom: 11,
            disableDefaultUI: true,
            zoomControl: true,
        });

        draw();
    }
    function addMarker(myLatLng) {
        var marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            title: 'Hello World!'
        });

        return marker;
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer></script>
<div class="col-lg-12" id="map">
<img src="/public/images/car-icon-DrivingClient-32.png">
<img src="/public/images/car-icon-Open-32.png">
<img src="/public/images/car-icon-Arrived-32.png">
<img src="/public/images/car-icon-Accepted-32.png">
</div>
<script>
    var map;
    var colors = {
        Dispatched: '#0012ff',
        Open: '#ff0000',
        Accepted: '#fff000',
        DrivingClient: '#20ce5e',
        Offline: '#000',
        Arrived: '#ff9600'
    };
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 53.907460714169574, lng: 27.713926315307596},
            zoom: 10,
            disableDefaultUI: true,
            zoomControl: true,
        });

        $.each(markers, function(index, value) {
            addMarker(value);
        });
    }

    function addMarker(driverStatus){
        driverStatus.latlng = new google.maps.LatLng(driverStatus.latitude, driverStatus.longitude);
        driverStatus.marker = new google.maps.Marker({
            position: driverStatus.latlng,
            map: map,
            icon: {
                url: getImage(driverStatus)
            },
            //title: driverStatus.driver_id
        });

        createInfowindow(driverStatus);
        createLine(driverStatus);
        //addLine(driver);
    }

    function createInfowindow(driverStatus){
        driverStatus.infowindow = new google.maps.InfoWindow({});
        setInfowindowContent(driverStatus);
        driverStatus.marker.addListener('click', function() {
            driverStatus.infowindow.open(map, driverStatus.marker);
        });
    }

    function setInfowindowContent(driverStatus){
        var content = '<div>'+ driverStatus.driver.name + '</div><div>!!!!Страус:!!!!! ' + driverStatus.status + '</div>'
        driverStatus.infowindow.setContent(content);
    }

    function getImage(driver){
        var image = '{{ asset('') }}public/images/car-icon-' + driver.status + '-32.png';
        return RotateIcon
                .makeIcon(image)
                .setRotation({deg: driver.course + 90})
                .getUrl();
    }

    function addLinePoint(driver, driverStatus){
        var lastLine = driver.lines.slice(-1)[0];
        if(lastLine.status == driverStatus.status){
            lastLine.line.getPath().push(driverStatus.latlng);
        } else {
            if(driver.lines.length>3){
               deleteFirstLine(driver);
            }
            createLine(driver);
        }

        return driver;
    }

    function deleteFirstLine(driver){
        var firstLine = driver.lines.shift();
        firstLine.line.setMap(null);
    }

    function createLine(driver){

        if(!driver.lines){
            driver.lines = [];
        }
        var line = new google.maps.Polyline({
            path: [driver.latlng],
            strokeColor: colors[driver.status],
            strokeOpacity: 1.0,
            strokeWeight: 5
        });
        line.setMap(map);
        driver.lines.push({status: driver.status, line: line});
    }

    function addLine(driver){

    }

    var markers = {!! $cars !!};
</script>
<script src="{{ asset('js/rotateIcon.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer></script>

{{--<script src="{{ asset('js/socket.io.js') }}"></script>--}}
<script src="{{ asset('js/socket.io.js') }}"></script>
<script>
    var socket = io('http://188.120.247.25:5556');
    socket.on("App\\Events\\DriversStatusChange", function(data){
        var driverStatus = data.driverStatus;
        var driver = markers[driverStatus.driver_id];

        driverStatus.latlng = new google.maps.LatLng(driverStatus.latitude, driverStatus.longitude);
        if( !driver) {
            driver = markers[driverStatus.driver_id] = addMarker(driverStatus);
        };
        driver.latlng = driverStatus.latlng;
        driver.status = driverStatus.status;
        driver.marker.setPosition(driverStatus.latlng);
        driver.marker.setIcon( getImage(driverStatus) );
        setInfowindowContent(driver);

        addLinePoint(driver, driverStatus);
    });
</script>
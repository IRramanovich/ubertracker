<script class="marker">
    var colors = {
        Dispatched: '#0012ff',
        Open: '#ff0000',
        Accepted: '#fff000',
        DrivingClient: '#20ce5e',
        Offline: '#000',
        Arrived: '#ff9600'
    };

    var shiftPath = [];

    function draw(){
        if (statuses.length != 0) {
            console.log('draw');
            shiftPath.push( createLineAndSetToMap(statuses[0]) );

            $.each(statuses, function(index, value) {
                lastStatus = statuses[index - 1];
                if(lastStatus && lastStatus.status != value.status){
                    shiftPath.push( createLineAndSetToMap(value) );
                    addPreviousLinePoint(value);
                } else {
                    addLinePoint(value);
                }

            });
        }
    }

    function createLineAndSetToMap(driverStatus){
        var latlng = new google.maps.LatLng(driverStatus.latitude, driverStatus.longitude);
        //var line = [];
        var line =  new google.maps.Polyline({
            path: [latlng ],
            strokeColor: colors[driverStatus.status],
            strokeOpacity: 1.0,
            strokeWeight: 5,
            trip_id: driverStatus.trip_id,
            id: driverStatus.id
        });
        line.setMap(map);
        return line;
    }

    function addPreviousLinePoint(driverStatus){
        if(shiftPath.length>1){
            var latlng = new google.maps.LatLng(driverStatus.latitude, driverStatus.longitude);
            shiftPath.slice(-2, -1)[0].getPath().push(latlng);
        }
    }

    function addLinePoint(driverStatus){
        var latlng = new google.maps.LatLng(driverStatus.latitude, driverStatus.longitude);
        shiftPath.slice(-1)[0].getPath().push(latlng);
    }

    var statuses = [];
</script>
@include('admin.map-init')
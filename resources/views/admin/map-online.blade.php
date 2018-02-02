<script src="{{ asset('js/rotateIcon.js') }}"></script>
<script src="{{ asset('js/socket.io.js') }}"></script>
<script async defer>
    var colors = {
        Dispatched: '#0012ff',
        Open: '#ff0000',
        Accepted: '#fff000',
        DrivingClient: '#20ce5e',
        Offline: '#000',
        Arrived: '#ff9600',
        Shadow: '#d3d3d3'
    };

    function draw(){
        OnlineMap.make(drivers);
    }

    var drivers = {!! $cars !!};


</script>
<script>
    var conn = new ab.Session(
            'ws://{{ $server }}:8089',
            function () {
                conn.subscribe('onNewData', function(topic, data){
                    // console.info('New data: topic_id: "' + topic + '"');
                    console.log(data.data.driver.name + ':' + data.data.status);
                    console.log('');

                    var point = data.data;

                    OnlineMap.calculateDistance(point);
                    OnlineMap.checkCarAndAddIfNotExists(point);
                    OnlineMap.addPoint(point);

                    // console.log('point:');
                    // console.log(point);
                });
            },

            function (code, reason, detail){
                console.warn('WebSocket connection closed: code=' + code + '; reason='+reason+'; detail='+detail);
            },

            {
//                        'maxRetries': 60,
//                        'retryDelay': 4000,
                'skipSubprotocolCheck': true
            }
    );
</script>

@include('admin.map-init')

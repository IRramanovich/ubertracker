var pathHistory = 4;

var OnlineMap = new function(drivers){

        this.drivers = {};
        this.cars = {};

        this.make = function (drivers){
            this.drivers = drivers;
            this.makeMarkers();
            return this;
        },

        this.makeMarkers = function(){
            $.each(this.drivers, function(index, driver) {
                OnlineMap.updateMarkerInfo(driver);
                $.each(driver.online, function (index, point) {
                    OnlineMap.addPoint(point);
                });
            });
            return this;
        },

        this.updateMarkerInfo = function(driver){
            var lastOnlinePoint = this.getLastOnlinePoint(driver);
            if(lastOnlinePoint){
                this.checkCarAndAddIfNotExists(lastOnlinePoint);
            }
        },

        this.checkCarAndAddIfNotExists = function(lastOnlinePoint){
            if(!this.cars[lastOnlinePoint.driver_id]){
                this.cars[lastOnlinePoint.driver_id] = {
                    latlng:  new google.maps.LatLng(lastOnlinePoint.latitude, lastOnlinePoint.longitude),
                    name: lastOnlinePoint.driver.name,
                    driver_id: lastOnlinePoint.driver_id,
                    status: lastOnlinePoint.status,
                    course: lastOnlinePoint.course,
                    path: [],
                    info: {}
                };
            }

            this.updateDriverMarker(lastOnlinePoint.driver_id);
        },

        this.updateDriverMarker = function(driver_id){
            var car = this.cars[driver_id];
            if(!car.marker){
                car.marker = new google.maps.Marker({
                    position: car.latlng,
                    map: map,
                });
                this.createInfowindow(driver_id);
            } else {
                this.updateInfowindow( driver_id );
            }
            car.marker.setPosition(car.latlng);
            car.marker.setIcon( getImage2(car) );
        },

        this.createInfowindow = function(driver_id){
            var car = this.cars[driver_id];
            var cars = this.cars;
            var visible_cars = [];
            car.info = new google.maps.InfoWindow({});
            this.setInfowindowContent(car);
            car.marker.addListener('click', function() {
                console.log(cars);
                car.info.open(map, car.marker);
                visible_cars.push(car.driver_id);
                for(car_id in cars){
                    if(car_id != car.driver_id && !find(visible_cars, car_id)){
                        cars[car_id].path.forEach(function(line, index){
                            line.setOptions({strokeOpacity: 0.1});
                        });
                    }
                };
            });
            car.info.addListener('closeclick', function() {
                for(car_id in cars){
                    if (car_id != car.driver_id) {
                        cars[car_id].path.forEach(function (line, index) {
                            line.setOptions({strokeOpacity: 1});
                        });
                    }
                }
            });
        },

        this.updateInfowindow = function(driver_id){
            var car = this.cars[driver_id];
            this.setInfowindowContent(car);
        },

        this.translateCarSatus = function (status) {
            switch (status) {
                case 'Open':
                    return 'Ждет заказ';
                    break;
                case 'Dispatched':
                    return 'Предложен заказ';
                    break;
                case 'Accepted':
                    return 'Едет к заказу';
                    break;
                case 'Arrived':
                    return 'Прибыл к заказу';
                    break;
                case 'DrivingClient':
                    return 'Выполняет заказ';
                    break;
                case 'Offline':
                    return 'Офлайн';
                    break;
                default:
                    return status;
            }
        },

        this.setInfowindowContent = function(car){
            var content = '<div>'+ car.name + '</div><div>Статус: ' + this.translateCarSatus(car.status) + '</div>';
            car.info.setContent(content);
        },

        this.calculateDistance = function(point){
            point.latlng = new google.maps.LatLng(point.latitude, point.longitude);
            var car = this.cars[point.driver_id];
            var time = Date.now();
            //performance.now();
            var interval = time - car.time;
            this.cars[point.driver_id].time = time;
            var distance = google.maps.geometry.spherical.computeDistanceBetween(car.latlng, point.latlng);
            // console.log(distance);
            // console.log(interval);
            if(!isNaN(interval) && (distance!=0)){
                var speed = (distance*0.001)/(interval*0.00000027);
                // console.log(speed);
                label = {
                    text: ' ' + point.speed,
                    fontSize: '13px',
                    labelClass: 'speedLabel'
                };
                car.marker.setLabel(label);
            }else if(isNaN(interval)){
                car.marker.setLabel('-');
            }

        },

        this.addPoint = function(point){
            point.latlng = new google.maps.LatLng(point.latitude, point.longitude);
            var car = this.cars[point.driver_id];
            if(car && car.path.length && car.status == point.status){
                car.path[0].getPath().push(point.latlng);
            } else {
                this.addLine(point);
                if(car.path.length>1){
                    car.path[1].getPath().push(point.latlng);
                }
            }
            car.status = point.status;
            car.latlng = point.latlng;
            car.course = point.course;
            car.labelClass = "labels";
            car.marker.setPosition(car.latlng);
            car.marker.setIcon( getImage2(car) );
            this.setInfowindowContent(car);
        },

        this.addLine = function(point){
            var line = new google.maps.Polyline({
                path: [{lat: point.latitude, lng: point.longitude}],
                strokeColor: colors[point.status],
                strokeOpacity: 1.0,
                strokeWeight: 5
            });
            line.setMap(map);

            var car = this.cars[point.driver_id] || [];
            car.path = car.path || [];
            car.path.unshift(line);
            this.checkLinesCount(car);
        },

        this.checkLinesCount = function(car){
            if(car.path.length>pathHistory){
                car.path.pop().setMap(null);
            }
        },

        this.getLastOnlinePoint = function(driver){
            return driver.online[0];
        }
};

function getImage2(car){
    return {
        // path: "M17.402,0H5.643C2.526,0,0,3.467,0,6.584v34.804c0,3.116,2.526,5.644,5.643,5.644h11.759c3.116,0,5.644-2.527,5.644-5.644 V6.584C23.044,3.467,20.518,0,17.402,0z M22.057,14.188v11.665l-2.729,0.351v-4.806L22.057,14.188z M20.625,10.773 c-1.016,3.9-2.219,8.51-2.219,8.51H4.638l-2.222-8.51C2.417,10.773,11.3,7.755,20.625,10.773z M3.748,21.713v4.492l-2.73-0.349 V14.502L3.748,21.713z M1.018,37.938V27.579l2.73,0.343v8.196L1.018,37.938z M2.575,40.882l2.218-3.336h13.771l2.219,3.336H2.575z M19.328,35.805v-7.872l2.729-0.355v10.048L19.328,35.805z",
        // path: "m6.40198,-25l-11.759,0c-3.117,0 -5.643,3.76186 -5.643,7.14396l0,37.76402c0,3.38101 2.526,6.12401 5.643,6.12401l11.759,0c3.116,0 5.644,-2.74191 5.644,-6.12401l0,-37.76402c-0.002,-3.3821 -2.528,-7.14396 -5.644,-7.14396zm4.655,15.39467l0,12.65708l-2.729,0.38086l0,-5.21474l2.729,-7.8232zm-1.432,-3.70544c-1.016,4.23169 -2.219,9.23376 -2.219,9.23376l-13.768,0l-2.222,-9.23376c0.001,0 8.884,-3.27467 18.209,0zm-16.877,11.87042l0,4.87405l-2.73,-0.37868l0,-12.31964l2.73,7.82428zm-2.73,17.60491l0,-11.24001l2.73,0.37217l0,8.89305l-2.73,1.97479zm1.557,3.19439l2.218,-3.61972l13.771,0l2.21901,3.61972l-18.20801,0zm16.75301,-5.5088l0,-8.5415l2.729,-0.38519l0,10.90256l-2.729,-1.97587z",
        path: "m6.40198,-29l-11.759,0c-3.117,0 -5.643,3.76186 -5.643,7.14396l0,37.76402c0,3.38101 2.526,6.12401 5.643,6.12401l11.759,0c3.116,0 5.644,-2.74191 5.644,-6.12401l0,-37.76402c-0.002,-3.3821 -2.528,-7.14396 -5.644,-7.14396zm4.655,15.39467l0,12.65708l-2.729,0.38086l0,-5.21474l2.729,-7.8232zm-1.432,-3.70544c-1.016,4.23169 -2.219,9.23376 -2.219,9.23376l-13.768,0l-2.222,-9.23376c0.001,0 8.884,-3.27467 18.209,0zm-16.877,11.87042l0,4.87405l-2.73,-0.37868l0,-12.31964l2.73,7.82428zm-2.73,17.60491l0,-11.24001l2.73,0.37217l0,8.89305l-2.73,1.97479zm1.557,3.19439l2.218,-3.61972l13.771,0l2.21901,3.61972l-18.20801,0zm16.75301,-5.5088l0,-8.5415l2.729,-0.38519l0,10.90256l-2.729,-1.97587z",
        scale: .6,
        strokeColor: 'white',
        strokeWeight: .10,
        fillOpacity: 1,
        fillColor: colors[car.status],
        offset: '5%',
        rotation: car.course,
        // The anchor for this image is the base of the flagpole at (0, 32).
        anchor: new google.maps.Point(0, 22)
    }
}
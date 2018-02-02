@extends('admin.layout')
@section('title')Статусы@endsection
@section('content')

    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 top-buffer status-table">
        <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover table-left-frame" id="shifts">
                <thead>
                <tr>
                    <th>Время последнего статуса</th>
                    <th>Текущий статус</th>
                    <th>Водитель</th>
                    <th>Начало смены</th>
                    <th>Офлайн в пик</th>
                    <th>Офлайн в не пик</th>
                    <th>Проп.</th>
                    <th>Суммарно заработал</th>
                    <th>Добавлено по пику</th>
                    <th>Поездок</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 top-buffer admin_map_online">
        @include('admin.map-online')
    </div>

    <div style="display:none">
        <div id="button_test" class="close_shift_button">
            {!! Form::open(['url' => route( 'admin.closeshift' ), 'method' => 'POST']) !!}
            {!! Form::token() !!}
            {!! Form::hidden('id','') !!}
            {!! Form::submit('x') !!}
            {!! Form::close() !!}
        </div>
    </div>

    <script>



        $('#shifts tbody').on('click', 'tr', function () {
            window.location = this.getAttribute('data-link');
        } );

        translateStatus = function(status) {
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
                default:
                    return status;
            }
        }

        toHHMMSS = function (num) {
            var sec_num = parseInt(num, 10); // don't forget the second param
            var hours   = Math.floor(sec_num / 3600);
            var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
            var seconds = sec_num - (hours * 3600) - (minutes * 60);

            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}
            return hours+':'+minutes;
        }

        getSurgeDuration = function(arr) {
            var sumSec = 0;
            arr.forEach(function(item, i, arr){
                sumSec = sumSec + item.offlineDuration;
            });
            return toHHMMSS(sumSec);
        }
        sortByName = function(a,b){
            if(a.driver.name > b.driver.name) return 1;
            if(a.driver.name < b.driver.name) return -1;
            return 0;
        }

        setInterval(function(){
            $.ajax({
                url: "/updatestatus",
                type: "GET",
                success: function (data) {
                    var online = [],
                        offline = data.shifts.offline;
                    for (var key in data.shifts.online){
                        online.push(data.shifts.online[key]);
                    }
                    online.sort(sortByName);
                    var elements = document.querySelector('#shifts tbody');
                    var innerTableData = '';
                    if(online) {
                        innerTableData = '<tr><td colspan="100%" align="center">Онлайн (' + online.length + ')</td></tr>';
                        for (var key in online) {
                            var offlines_not_surge = '00:00',
                                    offlines_surge = '00:00';
                            if (online[key].offlines_not_surge.length > 0) {
                                offlines_not_surge = getSurgeDuration(online[key].offlines_not_surge);
                            }
                            if (online[key].offlines_surge.length > 0) {
                                offlines_surge = getSurgeDuration(online[key].offlines_surge);
                            }
                            innerTableData = innerTableData +
                                    '<tr data-link="http://' + location.host + '/admin/shifts/' + online[key].id + '" class="' + online[key].last_change_status.status + '" > ' +
                                    '<td>' + online[key].last.substr(5) + '</td>' +
                                    '<td>' + translateStatus(online[key].last_change_status.status) + '</td>' +
                                    '<td>' + online[key].driver.name;
                            if(online[key].car){
                                innerTableData = innerTableData + ' ('+online[key].car.car_gov_number +')';
                            }
                            innerTableData = innerTableData + '</td>' +
                                    '<td>' + online[key].start.substr(5,11) + '</td>' +
                                    '<td>' + offlines_surge + '</td>' +
                                    '<td>' + offlines_not_surge + '</td>' +
                                    '<td>' + online[key].drop_order + '</td>' +
                                    '<td>' + online[key].total + '</td>' +
                                    '<td>' + online[key].surge + '</td>' +
                                    '<td>' + online[key].tripsCount + '</td>' +
                                    '<td> - </td>' +
                                    '</tr>';
                        }//for online
                    }
                    if (offline) {
                        innerTableData = innerTableData + '<tr><td colspan="100%" align="center">Оффлайн (' + offline.length + ')</td></tr>';
                        for (var key in offline) {
                            var offlines_not_surge = '00:00:00',
                                    offlines_surge = '00:00:00';
                            if (offline[key].offlines_not_surge.length > 0) {
                                offlines_not_surge = getSurgeDuration(offline[key].offlines_not_surge);
                            }
                            if (offline[key].offlines_surge.length > 0) {
                                offlines_surge = getSurgeDuration(offline[key].offlines_surge);
                            }
                            var buttonForm = document.querySelector('#button_' + offline[key].id);
                            if(buttonForm){
//                                console.log(buttonForm.innerHTML);
                            }else{
                                buttonForm = document.querySelector('#button_test'  );
                                input = buttonForm.getElementsByTagName('input')
                                for(i=0; i<input.length; i++){
                                    if(input[i].name == 'id'){
                                        input[i].value = offline[key].id;
                                    }
                                }
                            }

//                            console.log(offline[key].last);
                            innerTableData = innerTableData +
                                    '<tr data-link="http://' + location.host + '/admin/shifts/' + offline[key].id + '" class="' + offline[key].last_change_status.status + '">' +
                                    '<td>' + offline[key].last.substr(5) + '</td>' +
                                    '<td>' + offline[key].last_change_status.status + '</td>' +
                                    '<td>' + offline[key].driver.name;
                            if(offline[key].car){
                                innerTableData = innerTableData + ' ('+offline[key].car.car_gov_number +')';
                            }
                            innerTableData = innerTableData + '</td>' +
                                    '<td>' + offline[key].start.substr(5,11) + '</td>' +
                                    '<td>' + offlines_surge + '</td>' +
                                    '<td>' + offlines_not_surge + '</td>' +
                                    '<td>' + offline[key].drop_order + '</td>' +
                                    '<td>' + offline[key].total + '</td>' +
                                    '<td>' + offline[key].surge + '</td>' +
                                    '<td>' + offline[key].tripsCount + '</td>' +
                                    '<td id="button_' + offline[key].id + '" class="close_shift_button">' + buttonForm.innerHTML + '</td>' +
                                    '</tr>';
                        }
                    }
                    elements.innerHTML = innerTableData;
                    $('.close_shift_button').on('click', function(e){
                        $('#lock_screen').addClass('lock_screen_main');
                        $('.lock_screen_modal').show();
                    });
                }
            });

        }, 3000);

    </script>
    {{--<meta http-equiv="refresh" content="20" >--}}
@endsection
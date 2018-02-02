<html>
	<head>
		<title>Uber ИнкоПрофиль - @yield('title')</title>
        <meta name="csrf-token" content="{!! csrf_token() !!}" />
        <link rel="shortcut icon" href="https://d1a3f4spazzrp4.cloudfront.net/uber-com/1.2.15/d1a3f4spazzrp4.cloudfront.net/favicon-17677bc2ca.ico">

		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
		<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="/assets/yadcf/jquery.dataTables.yadcf.js" ></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="bower_components/az/dist/az.min.js"></script>
        <script src="{{ asset('js/autobahn.js') }}"></script>

        <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
		<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css">
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href=" {{ asset('css/sidebar.css') }}">
		<link rel="stylesheet" type="text/css" href=" {{ asset('css/admin.css') }}">
        <link rel="stylesheet" type="text/css" href=" {{ asset('css/logs.css') }}">
	</head>
	
	<body>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Uber</a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li class="{{ Menu::activeMenu('admin.status') }}"><a href="{{ route('admin.status') }}">Мониторинг</a></li>
                        <li class="{{ Menu::activeMenu('admin.shifts.index') }}"><a href="{{ route('admin.shifts.index') }}">Смены</a></li>
                        <li class="{{ Menu::activeMenu('admin.newshifts.index') }}"><a href="{{ route('admin.newshifts.index') }}">Аналитика</a></li>
                        <li class="{{ Menu::activeMenu('admin.index') }}"><a href="{{ route('admin.index') }}">Все статусы</a></li>
                        <li class="{{ Menu::activeMenu('admin.cars.index') }}"><a href="{{ route('admin.cars.index') }}">Автомобили</a></li>
                        <li class="{{ Menu::activeMenu('admin.surge') }}"><a href="{{ route('admin.surge') }}">Пиковые промежутки</a></li>
{{--                        <li class="{{ Menu::activeMenu('admin.logs') }}"><a href="{{ route('admin.logs') }}">Система</a></li>--}}
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="{{ Menu::activeMenu('admin.logout') }}"><a href="{{ route('admin.logout') }}">Выход</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div><!--/.container-fluid -->
        </nav>
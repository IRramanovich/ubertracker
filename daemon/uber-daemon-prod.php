<?php

require __DIR__.'/../bootstrap/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
	$request = Illuminate\Http\Request::capture()
);

use App\DriversStatus;
use App\Shifts;
use App\DriversLastStatus;
use App\DriversAllStatuses;
use App\Drivers;
use App\Offline;
use Carbon\Carbon;
use App\Events\DriversStatusChange;
use App\Lib\ShifControllLibrary;
use App\Service\UberDaemonLog;

$fp = fsockopen("ssl://partners.uber.com", 443, $errno, $errstr, 30);

if (!$fp) {
	echo "$errstr ($errno)<br />\n";
} else {
	$out = "GET /events/v2/fleets/ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a/supply?cityID=807 HTTP/1.1\r\n";
	$out .=	"Host: partners.uber.com\r\n";
	$out .=	"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:48.0) Gecko/20100101 Firefox/48.0\r\n";
	$out .=	"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
	$out .=	"Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3\r\n";
	$out .=	"Accept-Encoding: gzip, deflate, br\r\n";
	$out .=	'Cookie: marketing_vistor_id=f38ce0f8-c98f-4f20-86f5-29a0da0b55fb; optimizelyEndUserId=oeu1515668226777r0.7619328912198655; _ga=GA1.2.1136938785.1515668231; AMCVS_0FEC8C3E55DB4B027F000101%40AdobeOrg=1; aam_uuid=56234659657738681370454566934826949484; AAMC_uber_0=AMSYNCSOP%7C411-17550; udid=w_7508b9914f46443aac10a9074fc81b8c; sid=QA.CAESEDNClMYGLkQiiPeBFwHP8ekYvtS31gUiATEqJGRkY2UzNGVlLWE3NTctNGRkNS1iYTcyLWEyNmQxZTBjM2M2YTI85rc3tPA28jVXtYRzJNUPyc6zmCf7BDy3VZmhhUNKGMyGJIBruUguh6jSOHz8xmW68hwZnEA6fHztQCYHOgExQgh1YmVyLmNvbQ.OYD7Uj3x1jeXfPOTuoVpS-oKU-pnhuNEiyE_lljQy6c; csid=1.1523444287596.Gnq/fRBaIzzT04Axh7VBfZE3Z+vuzrd18n14EI/7j1g=; fsid=effi8alm-kihk-jtun-vvtp-zsx5u8v80a5a; aam_uuid=56234659657738681370454566934826949484; partners-platform-cookie=UKB0IT-jpqBObpJiaeK66g.YgdLOlnAUHtd070kz3Kvz-bMFbBwgfm7NFY_xWYDb39S0t06nGEnHzwlSN6etZmTzzFOCqYFmVMttfPg_krV2mLdmiW3kqZ6i_yRQDi-E9oqN_Et7EgXrG6yD-58wEfRTh88NsyJEBzHe70z9Gr12ptvVPxD6LDeiKaBnhQRZziaF9X-8Pzm3vv8IKzEWoHYZQyjjPJ9ZQckPjcX8WqJ3PmCPQM1c7SIRjFz3tqbzlqwS9NMTPipLDCYGTNq18pb8mZRS48u78O2zAJvmf3IFg7ltCXTP4U_GDT_odKAdGCuY5ugMOplSdgrHVDcBAXkm4PmeFerzkz6TTeUXvVIQ8FHNcWBfh_ujmv-pWfrrL6FIULd6DIxqfBz0CvRB1QEit6inBVnaJ5vjd2g4zyHIyRK2mpA_-rSIvNiyWYaw3S4T03NoNYKvztVGnLy5auTFDjjnYWCL6pJ1_JCrxWPUPY87mQci6lqMDL2wIe4xWdtPVEGP_PENXZ0MqUCg4HJEHHawOlIhGEOxVHD1JLoFmEOq0P1EW42fKqC8LoX8dIsLv-1n8hPEQModEm5QcdMQhkvuU5uCh6MD5DwdW7cLnCUi-MrVvOaDGhcFDS1najCYnHP7wGJaYSPPLEz6vY_ZQgZlp2mTT3JrOOehZD1vZZ0_vhHCdjGWihkua1rSg-X3m6jFulmcRPPMKdLFHakiuOljqaiIgNTvn9fNdeq7ZH9pn7wBU5vZVuEF6icfM00-tJjrkbFguCSU76Jy1y_.1516877654279.1209600000.li9XY2LsY7g0FWMtsJucBdOz5JlNEAJiFI9d3jabwzQ; _gid=GA1.2.1949946617.1517237230; _gat_UA_7157694_7=1; mp_e39a4ba8174726fb79f6a6c77b7a5247_mixpanel=%7B%22distinct_id%22%3A%20%22ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a%22%2C%22%24initial_referrer%22%3A%20%22%24direct%22%2C%22%24initial_referring_domain%22%3A%20%22%24direct%22%7D; utag_main=v_id:0160e4ddd0f10021f5753a2885f005078001b07000bd0$_sn:7$_ss:1$_st:1517239031057$segment:a$optimizely_segment:b$userid:ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a$ses_id:1517237231057%3Bexp-session$_pn:1%3Bexp-session; _gat_tealium_0=1; AMCV_0FEC8C3E55DB4B027F000101%40AdobeOrg=1611084164%7CMCMID%7C56731086461749576860503995213681293171%7CMCAAMLH-1517842031%7C6%7CMCAAMB-1517842031%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1517244431s%7CNONE%7CMCSYNCSOP%7C411-17568%7CMCAID%7CNONE%7CMCCIDH%7C-101741262; mp_mixpanel__c=2'."\r\n";
	$out .=	"Connection: keep-alive\r\n";
	$out .=	"Upgrade-Insecure-Requests: 1\r\n";
	$out .= "Connection: Close\r\n\r\n";
	fwrite($fp, $out);
}
$loop = React\EventLoop\Factory::create();

$read = new \React\Stream\Stream($fp, $loop);
$i = 0;

$read->on('data', function ($data, $read) use (&$i){
	$i++;

	$data = explode('data:', $data);
    for ($j=1; $j < count($data); $j++) {

        $oject_data = json_decode($data[$j], true);
        if (array_key_exists('supplyUUID', $oject_data)) {

            $driverStatus = DriversStatus::where('driver_id', $oject_data['supplyUUID'])
                ->with('driver')
                ->orderBy('id', 'desc')
                ->first();


            //check driver status
            if ($driverStatus && (!isset($driverStatus->driver) || !$driverStatus->driver->firstname) || !$driverStatus) {
                $driver = Drivers::firstOrNew([
                    'id' => $oject_data['supplyUUID'],
                ]);
                $uberDriver = \UberRequest::getDriver($oject_data['supplyUUID']);
                UberDaemonLog::ensure($uberDriver, UberDaemonLog::CODE_GET_DRIVER_FIRST_CASE);
                UberDaemonLog::log($uberDriver, UberDaemonLog::CODE_GET_DRIVER_FIRST_CASE, $uberDriver);
                if ($uberDriver) {
                    $name = explode(' ', $uberDriver->name);
                    $driver->fill([
                        'firstname' => $name[0],
                        'lastname' => $name[1],
                        'phone' => substr($uberDriver->mobile, -12),
                    ]);
                }
                $result = $driver->save();
                UberDaemonLog::ensure($result, UberDaemonLog::CODE_NEW_DRIVER_NOT_SAVE_OR_CREATE);

                if ($driverStatus) {
                    $driverStatus->load('driver');
                }
            }

            //save phone number by each driver
            $driver = Drivers::where('id', $oject_data['supplyUUID'])->first();

            //check open shift
            $shift = Shifts::where('driver_id', $oject_data['supplyUUID'])
                ->with('lastChangeStatus')
                ->whereNull('end')
                ->first();

            //check drop order current driver or not
            $result = ShifControllLibrary::checkDropOrder($driver, $oject_data['status']);
            UberDaemonLog::log($result, UberDaemonLog::CODE_CHECK_DROP_ORDER, $shift, $driverStatus);

            //create new shift or set shift status offline
            if (!$driverStatus || $driverStatus->status != $oject_data['status']) {
                $newStatus = DriversStatus::create(
                    [
                        'driver_id' => $oject_data['supplyUUID'],
                        'latitude' => $oject_data['latitude'],
                        'longitude' => $oject_data['longitude'],
                        'course' => $oject_data['course'],
                        'status' => $oject_data['status'],
                    ]
                );
                if (!$driverStatus || $driverStatus->status == 'Offline' || $driverStatus->status == 'shiftEnd') {
                    if ($driverStatus && $driverStatus->date->diffInHours(Carbon::now()) < 6 && $driverStatus->status != 'shiftEnd') {
                        UberNotification::offlineOnline($newStatus->driver->getName(), Carbon::now());
                        $shiftOffline = Shifts::where('driver_id', $oject_data['supplyUUID'])
                            ->whereNull('end')
                            ->orderBy('id', 'DESC')
                            ->first();
                        if($shiftOffline) {
                            Offline::createOffline($shiftOffline, $driverStatus, $newStatus);
                        }
                    } else {
                        //Check old shift
                        if(!$shift){
                            //Open new shift
                            UberNotification::newShift($newStatus, Carbon::now());
                            $result = Shifts::createNewShift($newStatus);//log
                            UberDaemonLog::ensure($result, UberDaemonLog::CODE_NEW_SHIFT_NO_CREATE);
                            UberDaemonLog::log($result, UberDaemonLog::CODE_NEW_SHIFT_CREATE, $result, $driverStatus);
                            $newStatus->status = 'shiftStart';
                            $newStatus->timestamps = false;
                            $newStatus->save();
                        }

                    }
                }
            }

            //check and create new shift
            $shift = Shifts::where('driver_id', $oject_data['supplyUUID'])
                ->with('lastChangeStatus')
                ->whereNull('end')
                ->first();

            if (!$shift) {
                $dr = DriversStatus::where('driver_id', $oject_data['supplyUUID'])
                    ->where('status', 'shiftStart')
                    ->orderBy('id', 'desc')
                    ->first();
                if ($dr->date->diffInHours(Carbon::now()) > 14){
                    $dr = DriversStatus::create(
                        [
                            'driver_id' => $oject_data['supplyUUID'],
                            'latitude' => $oject_data['latitude'],
                            'longitude' => $oject_data['longitude'],
                            'course' => $oject_data['course'],
                            'status' => $oject_data['status'],
                        ]
                    );
                }
                if ($dr) {
                    $shift = Shifts::createNewShift($dr);//log
                    UberDaemonLog::ensure($shift, UberDaemonLog::CODE_NEW_SHIFT_NO_CREATE_IN_RESERVE);
                    UberDaemonLog::log($shift, UberDaemonLog::CODE_NEW_SHIFT_CREATE_IN_RESERVE, $shift);
                }

            }

            //update laststatus table
            $lastStatus = DriversLastStatus::firstOrNew(['driver_id' => $oject_data['supplyUUID']]);
            $lastStatus->latitude = $oject_data['latitude'];
            $lastStatus->longitude = $oject_data['longitude'];
            $lastStatus->course = $oject_data['course'];
            $lastStatus->status = $oject_data['status'];
            $lastStatus->touch();
            $result = $lastStatus->save();//log
            UberDaemonLog::ensure($result, UberDaemonLog::CODE_LAST_STATUS_NOT_SAVE, $result);

            if ($shift->getCarId() == 0) {
                $lastStatus->load('driver');

                //add new status to allstatus table
                $allStatuses = new DriversAllStatuses();
                $allStatuses->driver_id = $oject_data['supplyUUID'];
                $allStatuses->latitude = $oject_data['latitude'];
                $allStatuses->longitude = $oject_data['longitude'];
                $allStatuses->course = $oject_data['course'];
                $allStatuses->status = $oject_data['status'];
                $allStatuses->shift_id = $shift->id;
                $result = $allStatuses->save();//log
                UberDaemonLog::ensure($result, UberDaemonLog::CODE_ALL_STATUS_NOT_SAVE, $result);

                $result_data = [
                    'topic_id'  => 'onNewData',
                    'data'      => $lastStatus
                ];
                \App\Classes\Socket\Pusher::sentDataToServer($result_data);
            }

        }
    }
});

$read->on('error', function (Exception $e){
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
});

$read->on('close', function () use (&$fp, $out){
    echo 'CLOSED';
    fwrite($fp, $out);
});

$loop->run();

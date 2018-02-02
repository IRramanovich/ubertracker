<?php

namespace App\Lib;

use App\DriversStatus;
use App\Shifts;
use Carbon\Carbon;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class UberRequest
{
    private $statusUrl = "/events/partner/minsk?cityid=807";

    private $dataUrl = "/p3/money/statements/view/current";

    private $statementsAllData = "/p3/money/statements/all_data/";

    private $closedStatement = "/p3/money/statements/view/";

    //private $drivers = "/p3/fleet/drivers";
    private $drivers = "/p3/fleet-manager/drivers";

    public function getHeaders(){
        $out = "GET /p3/money/statements/view/current HTTP/1.0\r\n";
        $out .= "Host: partners.uber.com\r\n";
        $out .= "Connection: keep-alive\r\n";
        $out .= "Cache-Control: max-age=0\r\n";
        $out .= "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Referer: https://partners.uber.com/p3/money/statements/index\r\n";
        $out .= "Accept-Encoding: gzip, deflate, sdch, br\r\n";
        $out .= "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4\r\n";
        $out .= 'Cookie: marketing_vistor_id=f38ce0f8-c98f-4f20-86f5-29a0da0b55fb; optimizelyEndUserId=oeu1515668226777r0.7619328912198655; _ga=GA1.2.1136938785.1515668231; AMCVS_0FEC8C3E55DB4B027F000101%40AdobeOrg=1; aam_uuid=56234659657738681370454566934826949484; AAMC_uber_0=AMSYNCSOP%7C411-17550; udid=w_7508b9914f46443aac10a9074fc81b8c; sid=QA.CAESEDNClMYGLkQiiPeBFwHP8ekYvtS31gUiATEqJGRkY2UzNGVlLWE3NTctNGRkNS1iYTcyLWEyNmQxZTBjM2M2YTI85rc3tPA28jVXtYRzJNUPyc6zmCf7BDy3VZmhhUNKGMyGJIBruUguh6jSOHz8xmW68hwZnEA6fHztQCYHOgExQgh1YmVyLmNvbQ.OYD7Uj3x1jeXfPOTuoVpS-oKU-pnhuNEiyE_lljQy6c; csid=1.1523444287596.Gnq/fRBaIzzT04Axh7VBfZE3Z+vuzrd18n14EI/7j1g=; fsid=effi8alm-kihk-jtun-vvtp-zsx5u8v80a5a; aam_uuid=56234659657738681370454566934826949484; partners-platform-cookie=UKB0IT-jpqBObpJiaeK66g.YgdLOlnAUHtd070kz3Kvz-bMFbBwgfm7NFY_xWYDb39S0t06nGEnHzwlSN6etZmTzzFOCqYFmVMttfPg_krV2mLdmiW3kqZ6i_yRQDi-E9oqN_Et7EgXrG6yD-58wEfRTh88NsyJEBzHe70z9Gr12ptvVPxD6LDeiKaBnhQRZziaF9X-8Pzm3vv8IKzEWoHYZQyjjPJ9ZQckPjcX8WqJ3PmCPQM1c7SIRjFz3tqbzlqwS9NMTPipLDCYGTNq18pb8mZRS48u78O2zAJvmf3IFg7ltCXTP4U_GDT_odKAdGCuY5ugMOplSdgrHVDcBAXkm4PmeFerzkz6TTeUXvVIQ8FHNcWBfh_ujmv-pWfrrL6FIULd6DIxqfBz0CvRB1QEit6inBVnaJ5vjd2g4zyHIyRK2mpA_-rSIvNiyWYaw3S4T03NoNYKvztVGnLy5auTFDjjnYWCL6pJ1_JCrxWPUPY87mQci6lqMDL2wIe4xWdtPVEGP_PENXZ0MqUCg4HJEHHawOlIhGEOxVHD1JLoFmEOq0P1EW42fKqC8LoX8dIsLv-1n8hPEQModEm5QcdMQhkvuU5uCh6MD5DwdW7cLnCUi-MrVvOaDGhcFDS1najCYnHP7wGJaYSPPLEz6vY_ZQgZlp2mTT3JrOOehZD1vZZ0_vhHCdjGWihkua1rSg-X3m6jFulmcRPPMKdLFHakiuOljqaiIgNTvn9fNdeq7ZH9pn7wBU5vZVuEF6icfM00-tJjrkbFguCSU76Jy1y_.1516877654279.1209600000.li9XY2LsY7g0FWMtsJucBdOz5JlNEAJiFI9d3jabwzQ; _gid=GA1.2.1949946617.1517237230; _gat_UA_7157694_7=1; mp_e39a4ba8174726fb79f6a6c77b7a5247_mixpanel=%7B%22distinct_id%22%3A%20%22ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a%22%2C%22%24initial_referrer%22%3A%20%22%24direct%22%2C%22%24initial_referring_domain%22%3A%20%22%24direct%22%7D; utag_main=v_id:0160e4ddd0f10021f5753a2885f005078001b07000bd0$_sn:7$_ss:1$_st:1517239031057$segment:a$optimizely_segment:b$userid:ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a$ses_id:1517237231057%3Bexp-session$_pn:1%3Bexp-session; _gat_tealium_0=1; AMCV_0FEC8C3E55DB4B027F000101%40AdobeOrg=1611084164%7CMCMID%7C56731086461749576860503995213681293171%7CMCAAMLH-1517842031%7C6%7CMCAAMB-1517842031%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1517244431s%7CNONE%7CMCSYNCSOP%7C411-17568%7CMCAID%7CNONE%7CMCCIDH%7C-101741262; mp_mixpanel__c=2'."\r\n";
        $out .= "Connection: Close\r\n\r\n";

        return $out;
    }

    public function  getStatementsHeadersByUrl($url){
        $out = "GET $url HTTP/1.0\r\n";
        $out .= "Host: partners.uber.com\r\n";
        $out .= "Connection: keep-alive\r\n";
        $out .= "Cache-Control: max-age=0\r\n";
        $out .= "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Referer: https://partners.uber.com/p3/money/statements/index\r\n";
        $out .= "Accept-Encoding: gzip, deflate, sdch, br\r\n";
        $out .= "Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4\r\n";
        $out .= 'Cookie: marketing_vistor_id=f38ce0f8-c98f-4f20-86f5-29a0da0b55fb; optimizelyEndUserId=oeu1515668226777r0.7619328912198655; _ga=GA1.2.1136938785.1515668231; AMCVS_0FEC8C3E55DB4B027F000101%40AdobeOrg=1; aam_uuid=56234659657738681370454566934826949484; AAMC_uber_0=AMSYNCSOP%7C411-17550; udid=w_7508b9914f46443aac10a9074fc81b8c; sid=QA.CAESEDNClMYGLkQiiPeBFwHP8ekYvtS31gUiATEqJGRkY2UzNGVlLWE3NTctNGRkNS1iYTcyLWEyNmQxZTBjM2M2YTI85rc3tPA28jVXtYRzJNUPyc6zmCf7BDy3VZmhhUNKGMyGJIBruUguh6jSOHz8xmW68hwZnEA6fHztQCYHOgExQgh1YmVyLmNvbQ.OYD7Uj3x1jeXfPOTuoVpS-oKU-pnhuNEiyE_lljQy6c; csid=1.1523444287596.Gnq/fRBaIzzT04Axh7VBfZE3Z+vuzrd18n14EI/7j1g=; fsid=effi8alm-kihk-jtun-vvtp-zsx5u8v80a5a; aam_uuid=56234659657738681370454566934826949484; partners-platform-cookie=UKB0IT-jpqBObpJiaeK66g.YgdLOlnAUHtd070kz3Kvz-bMFbBwgfm7NFY_xWYDb39S0t06nGEnHzwlSN6etZmTzzFOCqYFmVMttfPg_krV2mLdmiW3kqZ6i_yRQDi-E9oqN_Et7EgXrG6yD-58wEfRTh88NsyJEBzHe70z9Gr12ptvVPxD6LDeiKaBnhQRZziaF9X-8Pzm3vv8IKzEWoHYZQyjjPJ9ZQckPjcX8WqJ3PmCPQM1c7SIRjFz3tqbzlqwS9NMTPipLDCYGTNq18pb8mZRS48u78O2zAJvmf3IFg7ltCXTP4U_GDT_odKAdGCuY5ugMOplSdgrHVDcBAXkm4PmeFerzkz6TTeUXvVIQ8FHNcWBfh_ujmv-pWfrrL6FIULd6DIxqfBz0CvRB1QEit6inBVnaJ5vjd2g4zyHIyRK2mpA_-rSIvNiyWYaw3S4T03NoNYKvztVGnLy5auTFDjjnYWCL6pJ1_JCrxWPUPY87mQci6lqMDL2wIe4xWdtPVEGP_PENXZ0MqUCg4HJEHHawOlIhGEOxVHD1JLoFmEOq0P1EW42fKqC8LoX8dIsLv-1n8hPEQModEm5QcdMQhkvuU5uCh6MD5DwdW7cLnCUi-MrVvOaDGhcFDS1najCYnHP7wGJaYSPPLEz6vY_ZQgZlp2mTT3JrOOehZD1vZZ0_vhHCdjGWihkua1rSg-X3m6jFulmcRPPMKdLFHakiuOljqaiIgNTvn9fNdeq7ZH9pn7wBU5vZVuEF6icfM00-tJjrkbFguCSU76Jy1y_.1516877654279.1209600000.li9XY2LsY7g0FWMtsJucBdOz5JlNEAJiFI9d3jabwzQ; _gid=GA1.2.1949946617.1517237230; _gat_UA_7157694_7=1; mp_e39a4ba8174726fb79f6a6c77b7a5247_mixpanel=%7B%22distinct_id%22%3A%20%22ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a%22%2C%22%24initial_referrer%22%3A%20%22%24direct%22%2C%22%24initial_referring_domain%22%3A%20%22%24direct%22%7D; utag_main=v_id:0160e4ddd0f10021f5753a2885f005078001b07000bd0$_sn:7$_ss:1$_st:1517239031057$segment:a$optimizely_segment:b$userid:ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a$ses_id:1517237231057%3Bexp-session$_pn:1%3Bexp-session; _gat_tealium_0=1; AMCV_0FEC8C3E55DB4B027F000101%40AdobeOrg=1611084164%7CMCMID%7C56731086461749576860503995213681293171%7CMCAAMLH-1517842031%7C6%7CMCAAMB-1517842031%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1517244431s%7CNONE%7CMCSYNCSOP%7C411-17568%7CMCAID%7CNONE%7CMCCIDH%7C-101741262; mp_mixpanel__c=2'."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        return $out;
        //__qca=P0-1859747402-1473171168655; optimizelyEndUserId=oeu1473933680345r0.37048406454843064; drive_cta=Алексей Бабицкий; _RCRTX02=93dfa7f5-5974-42f5-955d-dffc9487b6d31473944462801; _RCRTX02_SESSION=a672d639-d734-441b-8e35-a2b6c53396631473944462801; __LOCALE__=ru_RU; _LOCALE_=ru_RU; _userid2=1f2d205b-3ccc-4519-bb1d-6d5776acb995; su=1; NaN_hash=a9271c40JKVUGNWF1473933705390; _ceg.s=odsvz0; _ceg.u=odsvz0; __ar_v4=NUHAF5SO45FFDIAUHO3O3R%3A20160916%3A1%7C4RSHDI3SQJBHRBSCVM4YPK%3A20160915%3A28%7CE4T7FAY7KNBC3MKS2TY73V%3A20160915%3A28%7C6HOPBDDQ75D7PDJOEL6FMQ%3A20160915%3A26%7CUN7GBNDA7VCC3OX2XXMMM3%3A20160916%3A1; optimizelySegments=%7B%22721332882%22%3A%22referral%22%2C%22722032887%22%3A%22false%22%2C%22722042994%22%3A%22gc%22%2C%222325660269%22%3A%22none%22%7D; optimizelyBuckets=%7B%7D; logged_in=true; user=%7B%22token%22%3A%221058b3d07b8cc4eecc3b13f15f63ec4e%22%2C%22role%22%3A%22partner%22%2C%22uuid%22%3A%22ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a%22%7D; _udid=w_5226421620b344fd8a26bab8e8a5edee; _ceg.s=oe9fk8; _ceg.u=oe9fk8; marketing_vistor_id=37a82952-ccd6-4ffe-b6c2-c8088776c47e; utag_main=v_id:0156ffd81a1e000c48c33cb54acc0506d001806500ac2$_sn:15$_ss:0$_st:1475144815036$segment:a$userid:ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a$flowtype:UberX$_pn:2%3Bexp-session$ses_id:1475142919407%3Bexp-session; session=928a629bbfc05e47_57f4d347.FTIGbzn84oeCI2x0YPBLj0KsSp0; partners-platform-cookie=V-2dZjqZjirbH9MzorA1kQ.xUOtMozR_XMfm3kHEgg3wfnRlYSloyuQdAP32-DIa21_qoBABKCY_OB5eQ7DOK_7AH2cUyXUHeuw9oCyUoUuVVhyRoCFAjUvSPVJvF_ec9YvrT1hbcv2KsYj1aH97mdmHIqSZmWNRgpejoicLCPpqa7msE-vHz0dAxYGmWTVbFnNjm9NQry4mz9hHYfc6qR78WSRCMNf7eiN_xr-wG1mQ_qyft_ihUFOGBUbCUMfLgxaYSXVoeDEJAsWmuyAvBWaJiVC78_8Md7vHDicMhOwStB1ApLpbUKwc_HeaDrjZYVs3pNGCSCCGWmb8_XdoSof4eQkNJfyS0k7cgqfKPqE0WWNYO54YExEL74gEl-u8ls.1475662663887.86400000.8aJLcDOdP7uIPCZCB5olmBKJYu_7B7BxAZiAPXGxdew; _gat_UA_7157694_7=1; _ga=GA1.2.1824316166.1473171168; mp_e39a4ba8174726fb79f6a6c77b7a5247_mixpanel=%7B%22distinct_id%22%3A%20%22156ffd81dcb17b-036a4693adfe02-4049042b-1aeaa0-156ffd81dcc360%22%2C%22__mps%22%3A%20%7B%7D%2C%22__mpso%22%3A%20%7B%7D%2C%22__mpa%22%3A%20%7B%7D%2C%22__mpu%22%3A%20%7B%7D%2C%22__mpap%22%3A%20%5B%5D%2C%22Lead%20Page%22%3A%20%22https%3A%2F%2Fwww.uber.com%2Fru%2F%22%2C%22%24search_engine%22%3A%20%22google%22%2C%22%24initial_referrer%22%3A%20%22https%3A%2F%2Fwww.google.pl%2F%22%2C%22%24initial_referring_domain%22%3A%20%22www.google.pl%22%2C%22user_id%22%3A%20%22ddce34ee-a757-4dd5-ba72-a26d1e0c3c6a%22%2C%22city%22%3A%20%22minsk%22%2C%22city_name%22%3A%20%22minsk%22%2C%22flowType%22%3A%20%22UberX%22%2C%22city_id%22%3A%20807%2C%22device_model%22%3A%20null%2C%22exp%22%3A%20%22a-int-p2%22%2C%22flow_type_name%22%3A%20%22UberX%22%2C%22from_signup%22%3A%20%221%22%2C%22language%22%3A%20%22ru_RU%22%2C%22onboarding_lite%22%3A%20false%2C%22os_version%22%3A%20%22%22%2C%22signed_in%22%3A%20false%7D; mp_mixpanel__c=4
    }

    public function getStatusesRequest()
    {
        return $this->getRequestString($this->statusUrl);
    }

    public function getDataRequest()
    {
        return $this->getRequestString($this->dataUrl);
    }

    public function send(){
        $fp = fsockopen("ssl://partners.uber.com", 443, $errno, $errstr, 30);
        fwrite($fp, $this->getHeaders());
        $str = '';
        while (!feof($fp)) {
            $str .= fgets($fp, 2048);
        }

        return json_decode( explode("\r\n\r\n", $str)[1] );
    }

    public function sendByUrl($url){
        $fp = fsockopen("ssl://partners.uber.com", 443, $errno, $errstr, 30);
        fwrite($fp, $this->getStatementsHeadersByUrl($url));
        $str = '';
        while (!feof($fp)) {
            $str .= fgets($fp, 2048);
        }

        return json_decode( explode("\r\n\r\n", $str)[1] );
    }

    public function sendByUrlHTML($url){
        $fp = fsockopen("ssl://partners.uber.com", 443, $errno, $errstr, 30);
        fwrite($fp, $this->getStatementsHeadersByUrl($url));
        $str = '';
        while (!feof($fp)) {
            $str .= fgets($fp, 2048);
        }

        return explode("\r\n\r\n", $str)[1];
    }

    public function getDriver($driver_id){;
        $html = $this->sendByUrlHTML($this->drivers);
//        preg_match("/JSON_GLOBALS_\[\"state\"\] = (.+?)\nwindow/", $html, $match);
        preg_match("/<script id=\"json-globals\" type=\"application\/json\">{(.*)}\<\/script>/", $html, $match);
        if(count($match) > 1){
            $drivers = json_decode('{'.$match[1].'}');

            if($drivers->state->drivers){
                foreach( $drivers->state->drivers->data->drivers as $driver){
                    if($driver->uuid == $driver_id){
                        return $driver;
                    }
                }
            }
            return NULL;
        }
        return false;
    }

    public function getShiftData(Shifts $shift){

        $lastWeekStatement = $this->getLastWeekStatement($shift);

        $response = $this->send();
        $currentStatement = $this->getDriverDataFromUberResponse($response, $shift);
        if(is_null($currentStatement) && is_null($lastWeekStatement)){
            return NULL;
        }
        $currentStatemenTrips = $currentStatement? (array)$currentStatement->trip_earnings->trips: [];
        $lastWeekStatementTrips = $lastWeekStatement? (array)$lastWeekStatement->trip_earnings->trips: [];
        return (object)array_merge($currentStatemenTrips, $lastWeekStatementTrips);
    }

    public function getLastWeekStatement(Shifts $shift){
        $lastStatement = $this->sendByUrl($this->statementsAllData)[0];
        if($lastStatement){
            $statementDate = Carbon::createFromTimestamp($lastStatement->ending_at);

            if( $statementDate->diffInDays(Carbon::now()) > 106 ){
                return NULL;
            }

            $lastStatementData = $this->sendByUrl($this->closedStatement . $lastStatement->uuid);
            return $this->getDriverDataFromUberResponse($lastStatementData, $shift);
        }
        return NULL;
    }

    public function getDriverDataFromUberResponse($data, $shift){
        if($data){
            if (is_object($data->body)){
                $drivers = $data->body->drivers;
                foreach($drivers as $one) {
                    if ($one->driver_uuid == $shift->driver_id) {
                        return $one;
                    }
                }
                return [];
            }
        }
        return NULL;
    }

    public function getSumm($data, Shifts $shift){
        $summ = 0.0;
        if($data) {
            foreach ($data->trip_earnings->trips as $one) {
                if (Carbon::parse($one->date)->timezone('Europe/Minsk')->between($shift->start, $shift->lastChangeStatus->date)) {
                    $summ += $one->total;
                }
            }
        }
        return $summ;
    }

    public function getSummNew($data, Shifts $shift){
        $summ = 0.0;
        if($data) {
            foreach ($data as $one) {
                if (Carbon::parse($one->date)->timezone('Europe/Minsk')->between($shift->start, $shift->lastChangeStatus->date)) {
                    $summ += $one->total;
                }
            }
        }
        return $summ;
    }

    public function getShipTripsByInterval($data, Carbon $start, Carbon $end)
    {
        $trips = [];
        if($data){
            foreach ($data->trip_earnings->trips as $one) {
                if( Carbon::parse($one->date)->timezone('Europe/Minsk')->between($start, $end) ){
                    $trips[] = $one;
                }
            }
        }

        return $trips;
    }
    public function getShipTripsByIntervalNew($data, Carbon $start, Carbon $end)
    {
        $trips = [];
        if($data){
            foreach ($data as $one) {
                if( Carbon::parse($one->date)->timezone('Europe/Minsk')->between($start, $end) ){
                    $trips[] = $one;
                }
            }
        }

        return $trips;
    }

    public function getSummByKey($array, $key){
        $ids = array_column($array, $key);
        return array_sum($ids);
    }
}
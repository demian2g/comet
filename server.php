<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/DB.php';

use GuzzleHttp\Client;

$db = DB::getDB();
$config = require "config.php";

if (is_file('config-local.php')) {
    $configFromFile = require 'config-local.php';
    if (isset($configFromFile['PHPProxy']))
        $config = array_merge_recursive($config, $configFromFile);
}

$app = new Comet\Comet($config['PHPProxy']);

$app->get('/kb4',
    function ($request, $response) use ($db) {
        $params = $request->getQueryParams();
        if (!empty($params)) {
            $signals = [];
            if (isset($params['amp']) && !empty($params['amp'])) {
                $signals[1633] = intval($params['amp']);
            }
            if (isset($params['n']) && !empty($params['n'])) {
                $signals[1490] = intval($params['n']);
            }
        }
        if (!empty($signals)) {
            $result = true;
            // INSERT INTO [ASUTP].[dbo].[ArhLastVal]([Time],[idSignal],[Value],[LastUpd]) VALUES ('08.08.2021 9:58:19',1633,71,'08.08.2021 9:58:19')
            foreach ($signals as $signal => $data) {
                $fetchReady = $db->query("INSERT INTO [ASUTP].[dbo].[ArhLastVal] ([Time],[idSignal],[Value],[LastUpd]) VALUES(:datetime, :signal, :datum, :datetime);", [
                    ':signal' => $signal,
                    ':datetime' => date('d.m.Y G:i:s'),
                    ':datum' => $data
                ]);
                $result = $result && $fetchReady;
            }
            return $response->withStatus($result ? 200 : 500);
        } else
            return $response->withStatus(400);
    }
);

$app->get('/kb7',
    function ($request, $response) use ($db) {
        $params = $request->getQueryParams();
        $where = [];
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $where[] =  '[' . $key . '] = ' . $value;
            }
            $where = join(' AND ', $where);
            $fetchReady = $db->query("SELECT * FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7] WHERE " . $where);
        } else {
            $fetchReady = $db->query("SELECT * FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7]");
        }
        if ($fetchReady) {
            $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
            return $response
//                ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
//                ->with(gzencode(json_encode($result)));
                ->with($result);
        } else {
            return $response
                ->with($db->errorInfo());
        }
    }
);

$app->get('/get',
    function ($request, $response) use ($db) {
        $params = $request->getQueryParams();
        if (isset($params['table']) && !empty($params['table'])) {
            $qParams = array_merge([], $params);
            unset($qParams['table']);
            if (DB::tableIsAllowed($params['table'])) {
                $where = [];
                if (!empty($qParams)) {
                    foreach ($qParams as $key => $value) {
                        $where[] =  $key . ' = ' . $value;
                    }
                    $where = join(' AND ', $where);
                    $fetchReady = $db->query("SELECT * FROM " . $params['table'] . " WHERE " . $where);
                } else
                    $fetchReady = $db->query("SELECT * FROM " . $params['table']);
                if ($fetchReady) {
                    $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
                    return $response
//                        ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
//                        ->with(gzencode(json_encode($result)));
                        ->with($result);
                } else {
                    return $response
                        ->with($db->errorInfo());
                }
            } else
                return $response
                    ->with('Wrong TableName');
        } else
            return $response
                ->with('provide query ?table=Schema.TableName');
});

$app->get('/datetime1',
    function ($request, $response) use ($db) {
        $params = $request->getQueryParams();
        if (!empty($params)) {
            $where = ['NPE > 0'];
            if (isset($params['start']) && isset($params['end'])) {
                $start          = $params['start'];
                $end            = $params['end'];
                $where[] = "(CAST(DAYRW as date) BETWEEN '" . $start . "' AND '". $end . "')";

                if (isset($params['start_time']) && isset($params['end_time'])) {
                    $start_time     = $params['start_time'];
                    $end_time       = $params['end_time'];
                    $where[] =  "(CAST(TIMRW as time) BETWEEN '" . $start_time . "' AND '". $end_time ."')";
                }
            }
            $fetchReady = $db->query("SELECT NPE, TIMRW, DAYRW, ID FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7] WHERE " . join(' AND ', $where) . " ORDER BY TIMRW ASC");
        } else {
            $fetchReady = $db->query("SELECT * FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7]");
        }
        if ($fetchReady) {
            $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
            return $response
//                ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
//                ->with(gzencode(json_encode($result)));
                    ->with($result);
        } else {
            return $response
                ->with($db->errorInfo());
        }
    }
);

$app->get('/datetime2',
    function ($request, $response) use ($db) {
        $params = $request->getQueryParams();
        // $where = "(CAST(DAYRW_ZL as date) BETWEEN '2021-04-30' AND '2021-04-30') AND (CAST(TIMRW_ZL as time) BETWEEN '08:00:00' AND '19:59:59') AND (NPE > 0) ORDER BY TIMRW_ZL ASC";

        if (!empty($params)) {
            $where = ['NPE > 0'];
            if (isset($params['start']) && isset($params['end'])) {
                $start          = $params['start'];
                $end            = $params['end'];
                $where[] = "(CAST(DAYRW_ZL as date) BETWEEN '" . $start . "' AND '". $end . "')";

                if (isset($params['start_time']) && isset($params['end_time'])) {
                    $start_time     = $params['start_time'];
                    $end_time       = $params['end_time'];
                    $where[] = "(CAST(TIMRW_ZL as time) BETWEEN '" . $start_time . "' AND '". $end_time ."')";
                }
            }
            $fetchReady = $db->query("SELECT NPE, TIMRW_ZL, DAYRW_ZL, ID FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7] WHERE " . join(' AND ', $where) . " ORDER BY TIMRW_ZL ASC");
        } else {
            $fetchReady = $db->query("SELECT * FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7]");
        }
        if ($fetchReady) {
            $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
            return $response
//                ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
//                ->with(gzencode(json_encode($result)));
                    ->with($result);
        } else {
            return $response
                ->with($db->errorInfo());
        }
    }
);

$app->get('/report', function ($request, $response){
    $DAYS_BEFORE = 7;
    $defaultParams = [
        'start' => time() - 60 * 60 * 24 * $DAYS_BEFORE,
        'end' => time(),
        'apikey' => 'DDE061B4423755BE8790842709C33A0A0000451C',
        'event_id' => 24,
        'index' => 5
    ];
    $params = array_merge($defaultParams, $request->getQueryParams());
    $headers = [
        'Accept' => 'application/json',
        'Content-Type' => ' application/json',
        'X-WH-APIKEY' => $params['apikey'],
        'X-WH-START' => $params['start'],
        'X-WH-END' => $params['end']
    ];
    $client = new Client([
        'base_uri' => 'https://batareya77.webhmicloud.com',
        'headers' => $headers
    ]);
    $clientResponse = $client->get('/api/event-data/' . $params['event_id']);
    $reportResponse = [];
    if ($clientResponse->getStatusCode() === 200) {
        $jsonResponse = $clientResponse->getBody();
        if ($jsonResponse && $data = json_decode($jsonResponse, true)) {
            $data = array_filter($data, function ($rep) use ($params) {
                return $rep['end_time'] > 0 && $rep['xtra_regs'][$params['index']]['E' . $params['event_id'] . '.' . $params['index']] > 0;
            });
            if (count($data) > 0) {
                foreach ($data as $item) {
                    $p = intval($item['xtra_regs'][$params['index']]['E' . $params['event_id'] . '.' . $params['index']]);
                    $date = date('Ymd', $item['start_time']);
                    if (!array_key_exists($p, $reportResponse))
                        $reportResponse[$p] = [];
//                    $reportResponse[$p][$date] = array_map(function ($v){return array_values($v)[0];}, $item['xtra_regs']);
                    $reportResponse[$p][$date] = [
                        intval($date),
                        $p,
                        $item['xtra_regs'][0]['E' . $params['event_id'] . '.0'],
                        $item['xtra_regs'][4]['E' . $params['event_id'] . '.4'],
                        $item['xtra_regs'][3]['E' . $params['event_id'] . '.3'],
                        $item['xtra_regs'][2]['E' . $params['event_id'] . '.2'],
                    ];
                }
                ksort($reportResponse);
            }
        }
    }
    return $response
//        ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
//        ->with(gzencode(json_encode($reportResponse)));
        ->with($reportResponse);
});

$app->run();
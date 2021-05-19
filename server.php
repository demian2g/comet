<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/DB.php';

$db = DB::getDB();
$config = require "config.php";

if (is_file('config-local.php')) {
    $configFromFile = require 'config-local.php';
    if (isset($configFromFile['PHPProxy']))
        $config = array_merge_recursive($config, $configFromFile);
}

$app = new Comet\Comet($config['PHPProxy']);

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

// $app->get('/today',
//     function ($request, $response) use ($db) {
//         $params = $request->getQueryParams();
//         $where = [];
//         if (!empty($params)) {
//             foreach ($params as $key => $value) {
//                 $where[] =  '[' . $key . '] = ' . $value;
//             }
//             $where = join(' AND ', $where);
//             $fetchReady = $db->query("SELECT * FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7] WHERE " . $where);
//         } else {
//             $fetchReady = $db->query("SELECT * FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7]");
//         }
//         if ($fetchReady) {
//             $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
//             return $response
// //                ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
// //                ->with(gzencode(json_encode($result)));
//                 ->with($result);
//         } else {
//             return $response
//                 ->with($db->errorInfo());
//         }
//     }
// );

// by igor_rubens
// $app->get('/today',
//     function ($request, $response) use ($db) {
//         // return $response
//         //             ->with('Custom Sample TableName');
//         $params = $request->getQueryParams();

//         $where = "(CAST(TIMRW as time) BETWEEN '08:00:00' AND '19:59:59') AND (NPE > 0)";
//         if (!empty($params)) {
//             if ($params['start'] && $params['end']) {
//                 $start = $params['start'];
//                 $end = $params['end'];

//                 $where .= " AND (CAST(DAYRW as date) BETWEEN '" . $start . "' AND '". $end . "')";
//             }

//             //foreach ($params as $key => $value) {
//                 // $where[] =  '[' . $key . '] = ' . $value;
                
//                 // (CAST(DAYRW as date) BETWEEN '2021-04-28' AND '2021-04-28') AND (CAST(TIMRW as time) BETWEEN '08:00:00' AND '19:59:59') AND (NPE > 0)
//                 // $where = "(CAST(DAYRW as date) BETWEEN '2021-04-28' AND '2021-04-28') AND (CAST(TIMRW as time) BETWEEN '08:00:00' AND '19:59:59') AND (NPE > 0)";
//             //}
//             // $where = join(' AND ', $where);
//             $fetchReady = $db->query("SELECT * FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7] WHERE " . $where);
//         } else {
//             $fetchReady = $db->query("SELECT * FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7]");
//         }
//         if ($fetchReady) {
//             $result = $fetchReady->fetchAll(PDO::FETCH_ASSOC);
//             return $response
// //                ->withHeaders([ 'Content-Type' => 'application/json; charset=utf-8', 'Content-Encoding' => 'gzip' ])
// //                ->with(gzencode(json_encode($result)));
//                     ->with($result);
//         } else {
//             return $response
//                 ->with($db->errorInfo());
//         }
//     }
// );

$app->get('/datetime1',
    function ($request, $response) use ($db) {
        $params = $request->getQueryParams();
        if (!empty($params)) {
            $where = ['NPE > 0'];
            if ($params['start'] && $params['end']) {
                $start          = $params['start'];
                $end            = $params['end'];
                $where[] = "(CAST(DAYRW as date) BETWEEN '" . $start . "' AND '". $end . "')";

                if ($params['start_time'] && $params['end_time']) {
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
        $where = "(CAST(TIMRW_ZL as time) BETWEEN '";

        if (!empty($params)) {
            if ($params['start'] && $params['end']) {
                
                $start_time     = $params['start_time'];
                $end_time       = $params['end_time'];
                $where .=  $start_time . "' AND '". $end_time ."') AND (NPE > 0)";

                $start          = $params['start'];
                $end            = $params['end'];
                $where .= " AND (CAST(DAYRW_ZL as date) BETWEEN '" . $start . "' AND '". $end . "')";
            }
            
            $fetchReady = $db->query("SELECT NPE, TIMRW_ZL, DAYRW_ZL, ID FROM [YKOKS-S-SQL2005.IN.YKOKS.LOCAL].[DCM].[dbo].[KB7] WHERE " . $where . " ORDER BY TIMRW_ZL ASC");
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


$app->run();

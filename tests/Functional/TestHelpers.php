<?php

namespace PackBot\Tests\Functional;

use PackBot\Environment;

class TestHelpers
{
    public static function emptyDB(array $credentials)
    {
        $dsn = 'mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['database'];

        if (!empty($credentials['port'])) {
            $dsn .= ';port=' . $credentials['port'];
        }

        $options = [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];

        $pdo = new \PDO($dsn, $credentials['user'], $credentials['password'], $options);

        $tables = [];
        $stmt   = $pdo->query('SHOW TABLES');

        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0;');
            $pdo->prepare('DELETE FROM `' . $table . '`;')->execute();
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Inserts multiple example incidents into the "incidents" table.
     * Incidents:
     * 1. Incident type "wrongCode", code 404. Duration is 1 minute. ID is 1. 60 seconds.
     * 2. Incident type "timeout", timeout 10 seconds. Duration is 10 seconds. ID is 2. 10 seconds.
     * 3. Incident type "timeout", timeout 15 seconds. Duration is 2 days 3 hours. ID is 3. 183600 seconds.
     *
     * Average time in seconds should be 61223.
     * Total time in seconds should be 183670.
     */
    public static function insertExampleIncidents(int $site_id)
    {
        $credentials = [
            'host'     => Environment::var('db_host'),
            'user'     => Environment::var('db_user'),
            'password' => Environment::var('db_password'),
            'database' => Environment::var('db_name'),
        ];

        $dsn = 'mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['database'];

        if (!empty($credentials['port'])) {
            $dsn .= ';port=' . $credentials['port'];
        }

        $options = [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];

        $pdo = new \PDO($dsn, $credentials['user'], $credentials['password'], $options);

        // Prepare the SQL statement to insert incidents
        $sql  = 'INSERT INTO incidents (id, site_id, start_time, end_time, data) VALUES (:id, :site_id, :start_time, :end_time, :data)';
        $stmt = $pdo->prepare($sql);

        // Incident 1
        $id1         = 1;
        $start_time1 = date('Y-m-d H:i:s', strtotime('-1 minute'));
        $end_time1   = date('Y-m-d H:i:s');
        $data1       = json_encode(['type' => 'wrongCode', 'code' => 404]);
        $stmt->execute([':id' => $id1, ':site_id' => $site_id, ':start_time' => $start_time1, ':end_time' => $end_time1, ':data' => $data1]);

        // Incident 2
        $id2         = 2;
        $start_time2 = date('Y-m-d H:i:s', strtotime('-10 seconds'));
        $end_time2   = date('Y-m-d H:i:s');
        $data2       = json_encode(['type' => 'timeout', 'timeout' => 10]);
        $stmt->execute([':id' => $id2, ':site_id' => $site_id, ':start_time' => $start_time2, ':end_time' => $end_time2, ':data' => $data2]);

        // Incident 3
        $id3         = 3;
        $start_time3 = date('Y-m-d H:i:s', strtotime('-2 days -3 hours'));
        $end_time3   = date('Y-m-d H:i:s');
        $data3       = json_encode(['type' => 'timeout', 'timeout' => 15]);
        $stmt->execute([':id' => $id3, ':site_id' => $site_id, ':start_time' => $start_time3, ':end_time' => $end_time3, ':data' => $data3]);
    }
}

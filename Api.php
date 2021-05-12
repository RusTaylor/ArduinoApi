<?php


class Api
{
    /** @var PDO */
    private $db;

    public function __construct()
    {
        $dsn = "pgsql:host=arduino_postgres;dbname=api";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->db = new PDO($dsn, 'rustaylor', '9621', $opt);
    }

    public function saveMonitor($request)
    {
        $arduinoData = $this->getOrCreateArduinoIdByName($request['name']);

        $queryValues = [];
        foreach ($request['monitor'] as $paramName => $paramValue) {
            $queryValues[] = "({$arduinoData['id']},'$paramName', $paramValue)";
        }

        $this->db->query(
            "INSERT INTO arduino_param (arduino_id, param_name, param_value) VALUES " .
            implode(',', $queryValues) .
            " ON CONFLICT(arduino_id, param_name) DO UPDATE SET param_value=excluded.param_value"
        );
    }

    public function getAndChangeParams($request)
    {
        $arduinoData = $this->getOrCreateArduinoIdByName($request['name']);

        $queryValues = [];
        foreach ($request['control'] as $paramName => $paramType) {
            $queryValues[] = "({$arduinoData['id']}, '$paramName', '$paramType', 0)";
        }

        $this->db->query(
            "INSERT INTO arduino_control_param (arduino_id, param_name, param_type, param_value) VALUES " .
            implode(',', $queryValues) .
            " ON CONFLICT(arduino_id, param_name) DO NOTHING"
        );

        $stmt = $this->db->query(
            "SELECT param_name, param_value FROM arduino_control_param where arduino_id = {$arduinoData['id']}"
        );

        $response = [];
        while ($row = $stmt->fetch()) {
            $response[$row['param_name']] = $row['param_value'];
        }

        return $response;
    }

    public function getData()
    {
        $stmt = $this->db->query("SELECT an.name, ap.param_name, ap.param_value from arduino_name an
                                    LEFT JOIN arduino_param ap on an.id = ap.arduino_id
                                    order by ap.param_name");
        $data = [];
        while ($row = $stmt->fetch()) {
            $data[$row['name']]['monitor'][] = ['name' => $row['param_name'], 'value' => $row['param_value']];
        }

        $stmt = $this->db->query("SELECT an.name, acp.param_name, acp.param_value, acp.param_type from arduino_name an
                                    LEFT JOIN arduino_control_param acp on an.id = acp.arduino_id
                                     order by acp.param_name");
        while ($row = $stmt->fetch()) {
            $data[$row['name']]['control'][] = ['name' => $row['param_name'], 'value' => $row['param_value'], 'type' => $row['param_type']];
        }

        return $data;
    }

    public function changeData($request)
    {
        $params = explode('_', $request['id']);
        $arduinoId = $this->getOrCreateArduinoIdByName($params[0])['id'];
        $value = $request['value'];

        $this->db->query("UPDATE arduino_control_param set param_value = {$value} where arduino_id = {$arduinoId} and param_name = '{$params[1]}'");
    }

    public function deleteArduino($request)
    {
        if (empty($request)) {
            return ['status' => 'error'];
        }

        $stmt = $this->db->query("SELECT id FROM arduino_name WHERE name = '" . $request['arduinoName'] . "'");

        $id = ($stmt->fetch()['id']) ?? 0;

        if ($id == 0) {
            return ['status' => 'error'];
        }

        $this->db->query("DELETE FROM arduino_control_param WHERE arduino_id = $id");
        $this->db->query("DELETE FROM arduino_param WHERE arduino_id = $id");
        $this->db->query("DELETE FROM arduino_name WHERE id = $id");

        return ['status' => 'ok'];
    }

    private function getOrCreateArduinoIdByName($name)
    {
        $stmt = $this->db->query("SELECT id FROM arduino_name where name = '{$name}'");

        $arduinoData = $stmt->fetch();

        if (empty($arduinoData)) {
            $stmt = $this->db->query("INSERT INTO arduino_name (name) values ('{$name}') returning id");
            $arduinoData = $stmt->fetch();
        }

        return $arduinoData;
    }
}
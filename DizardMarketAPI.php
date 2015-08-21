<?php

class SignatureGenerator{

    private $salt = "";
    private $ignoreInvalidValues = false;
    private $ignoreLevelDeeperThan = 2;

    public function __construct($salt, $ignoreUnsupportedValues = false) {
        if (!is_string($salt) || !$salt)
            throw new InvalidArgumentException("\$salt required to be non empty string!");
        if (!is_bool($ignoreUnsupportedValues))
            throw new InvalidArgumentException("\$ignoreUnsupportedValues required to be boolean!");
        $this->salt = $salt;
        $this->ignoreInvalidValues = $ignoreUnsupportedValues;
    }

    public function assemble(array $params) {
        if (empty($params))
            throw new InvalidArgumentException("Empty params passed!");

        return sha1($this->parseArray($params, 1) . ";" . $this->salt);
    }

    private function parseArray(array $params, $level) {
        if ($level > $this->ignoreLevelDeeperThan)
            return "";
        $paramsToSign = array();
        ksort($params);
        unset($params["signature"]);
        foreach ($params as $key => $value) {
            $valueToAdd = "";
            switch (true) {
                case is_bool($value):
                    $valueToAdd = $value ? "1" : "0";
                    break;
                case is_scalar($value) && !is_resource($value):
                    $valueToAdd = (string)$value;
                    break;
                case is_array($value):
                    $valueToAdd = $this->parseArray($value, $level + 1);
                    break;
                case is_null($value):
                    break;
                default:
                    if (!$this->ignoreInvalidValues)
                        throw new InvalidArgumentException("Type of value for key: \"{$key}\" is not supported." . " Supported types are: boolean, string, array and null");
                    continue 2;
            }
            if ($valueToAdd === "")
                continue;
            $paramsToSign[$key] = $key . ':' . $valueToAdd;
        }

        return implode(";", $paramsToSign);
    }
}

class DizardMarketAPI {

    private $_uid;
    private $_salt;

    public function __construct($uid, $salt) {
        $this->_uid = $uid;
        $this->_salt = $salt;
    }

    private function sendQuery($params) {
        unset($params['uid'], $params['signature']);

        $params['uid'] = $this->_uid;

        // genertae signature
        $SGenerator = new SignatureGenerator($this->_salt);
        $params['signature'] = $SGenerator->assemble($params);

        $url = 'http://dizard-test.boombet.ru/dota/market/v1/api';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    public function getMyInventory() {
        return $this->sendQuery([
            'action' => 'getMyInventory'
        ]);
    }

    public function getMyLots() {
        return $this->sendQuery([
            'action' => 'getMyLots'
        ]);
    }

    public function getTrades($from=0) {
        return $this->sendQuery([
            'from'   => $from,
            'action' => 'getTrades'
        ]);
    }

    public function getBalance() {
        return $this->sendQuery([
            'action' => 'getBalance'
        ]);
    }

    public function addLot($id_gl, $cost) {
        return $this->sendQuery([
            'action' => 'addLot',
            'id_gl'  => $id_gl,
            'cost'   => $cost
        ]);
    }

    public function deleteLot($id) {
        return $this->sendQuery([
            'action' => 'deleteLot',
            'id' => $id
        ]);
    }

    public function changeLotCost($id, $cost) {
        return $this->sendQuery([
            'action' => 'changeLotCost',
            'id' => $id,
            'cost' => $cost
        ]);
    }

    public function getLot($id) {
        return $this->sendQuery([
            'action' => 'getLot',
            'id' => $id
        ]);
    }

    public function needGiveItems() {
        return $this->sendQuery([
            'action' => 'needGiveItems'
        ]);
    }
}
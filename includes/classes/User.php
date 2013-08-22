<?php

/**
 * Class User
 */
class User
{
    protected $_id;
    protected $_data;

    /**
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->_id = $id;
        if (!is_null($id)) {
            $this->load($id);
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        if ($key != 'id') {
            $this->_data[$key] = $value;
        }
    }

    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->_data[$key];
    }

    /**
     * @param $value
     * @param string $field
     */
    public function load($value, $field = 'id')
    {
        $db = DbFactory::getInstance();
        $query = "SELECT * FROM users WHERE $field = :$field LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute(array(":$field" => $value));
        $result = $stmt->fetch();

        foreach ($result as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param $user
     * @param $pass
     * @param array $data
     */
    public static function createUser($user, $pass, $data = array())
    {
        $u = new static;
        $u->_data = $data;
        $u->user = $user;
        $u->pass = $u->createHash($pass);

        $u->save();
    }

    public function save()
    {
        $params = $setParams = array();

        $db = DbFactory::getInstance();
        $action = $this->_id ? 'UPDATE' : 'INSERT INTO';
        $where = $this->_id ? (" WHERE id = " . $this->_id):'';

        $query = $action . " users SET ";
        foreach ($this->_data as $key => $value) {
            $setParams[] = " $key = :$key ";
            $params[$key] = $value;
        }
        $query .= implode(',', $setParams);
        $query .= $where;

        $stmt = $db->prepare($query);
        $stmt->execute($params);

    }

    /**
     * @return $this|bool
     */
    public function login()
    {
        $db = DbFactory::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE user = :user LIMIT 1");
        $success = $stmt->execute(
            array(
                ':user' => $this->user
            )
        );
        $result = $stmt->fetch();

        if (!$result) {
            return false;
        }

        if (!$this->validatePassword($this->pass, $result['pass'])) {
            return false;
        }

        $this->setUserSession($result['id']);
        $this->_id = $result['id'];

        unset($result['id']);
        $this->_data = $result;


        return $this;
    }

    /**
     * @param $id
     */
    public function setUserSession($id)
    {
        $_SESSION['user_id'] = $id;
    }

    public static function isAuthorized()
    {
        $userId = static::getUserSession();
        if (empty($userId)) {
            header('Location:login.php?r=' . rawurlencode($_SERVER['REQUEST_URI']));
        }
        return new static($userId);
    }

    /**
     * @return mixed
     */
    public static function getUserSession()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return $_SESSION['user_id'];
    }


    /**
     * @param $password
     * @return string
     */
    public function createHash($password)
    {
        // format: algorithm:iterations:salt:hash
        $salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
        return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" . $salt . ":" .
        base64_encode(
            $this->pbkdf2(
                PBKDF2_HASH_ALGORITHM,
                $password,
                $salt,
                PBKDF2_ITERATIONS,
                PBKDF2_HASH_BYTE_SIZE,
                true
            )
        );
    }

    /**
     * @param $password
     * @param $correctHash
     * @return bool
     */
    public function validatePassword($password, $correctHash)
    {
        $params = explode(":", $correctHash);
        if (count($params) < HASH_SECTIONS)
            return false;
        $pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
        return $this->slowEquals(
            $pbkdf2,
            $this->pbkdf2(
                $params[HASH_ALGORITHM_INDEX],
                $password,
                $params[HASH_SALT_INDEX],
                (int)$params[HASH_ITERATION_INDEX],
                strlen($pbkdf2),
                true
            )
        );
    }

    /**
     * Compares two strings $a and $b in length-constant time.
     * 
     * @param $a
     * @param $b
     * @return bool
     */
    public function slowEquals($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }

    /**
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     * $algorithm - The hash algorithm to use. Recommended: SHA256
     * $password - The password.
     * $salt - A salt that is unique to the password.
     * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
     * $keyLength - The length of the derived key in bytes.
     * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
     * Returns: A $keyLength-byte key derived from the password and salt.
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     */    
    public function pbkdf2($algorithm, $password, $salt, $count, $keyLength, $rawOutput = false)
    {
        $algorithm = strtolower($algorithm);
        if (!in_array($algorithm, hash_algos(), true))
            die('PBKDF2 ERROR: Invalid hash algorithm.');
        if ($count <= 0 || $keyLength <= 0)
            die('PBKDF2 ERROR: Invalid parameters.');

        $hashLength = strlen(hash($algorithm, "", true));
        $blockCount = ceil($keyLength / $hashLength);

        $output = "";
        for ($i = 1; $i <= $blockCount; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }

        if ($rawOutput) {
            return substr($output, 0, $keyLength);
        } else {
            return bin2hex(substr($output, 0, $keyLength));
        }
    }

}
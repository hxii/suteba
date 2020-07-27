<?php
/*
捨場  —  Suteba
https://paulglushak.com/suteba
*/

define('DUMP_DIR', 'data');
define('DS', DIRECTORY_SEPARATOR);

class Suteba
{

    /**
     * Allowed keys (or buckets)
     *
     * @var array keys
     */
    private $keys = [
        'test' => '098f6bcd4621d373cade4e832627b4f6',
        'poato' => 'potato'
    ];

    public $requestKey, $requestKeyholder, $requestPayload, $requestAction, $requestType,
    $requestMethod, $requestTime, $requestAgent, $requestSource;

    public function __construct()
    {
        if (isset($_GET['key']) && $this->isAllowedToRequest($_GET['key'])) {
            /* If the key is allowed, save info and handle the request */
            $this->requestKey = $_GET['key'];
            $this->requestKeyholder = $this->getKeyholder($this->requestKey);
            if (isset($_GET['action'])) {
                $this->requestAction = $_GET['action'];
            }
            $this->requestPayload = file_get_contents('php://input');
            $this->requestMethod = $_SERVER['REQUEST_METHOD'];
            $this->requestType = $_SERVER['CONTENT_TYPE'];
            $this->requestTime = date("l jS F \@ g:i:s a", $_SERVER['REQUEST_TIME']);
            $this->requestAgent = $_SERVER['HTTP_USER_AGENT'];
            $this->requestSource = $_SERVER['REMOTE_ADDR'];
            $this->handleRequest($this->requestKey, $this->requestAction);
        } else {
            /* If no key, or key not allowed return 401 */
            $this->response(401);
        }
    }

    /**
     * Is the key allowed?
     *
     * @param string $key
     * @return boolean
     */
    private function isAllowedToRequest(string $key)
    {
        return in_array($key, array_values($this->keys));
    }

    /**
     * Return key "label" or "owner"
     *
     * @param string $key
     * @return void
     */
    private function getKeyholder(string $key)
    {
        return array_keys($this->keys, $key)[0];
    }

    /**
     * Handle the request based on the action. Default is to dump the request.
     * action=list will return all available requests under a key
     *
     * @param string $key
     * @param string $action
     * @return void
     */
    private function handleRequest(string $key, $action = '')
    {
        echo $action;
        switch ($action) {
            case 'list':
                $this->getRequests($key);
            break;
            exit;

            case '':
                $data = $this->prepareRequestDump();
                $filename = $this->getUUID();
                $dump = $this->dumpRequest($this->requestKey, $filename, $data);
                if ($dump) {
                    /* Return request ID and path to it */
                    $message = <<<EOD
                    {
                        "id": "{$dump['id']}",
                        "path": "{$dump['path']}"
                    }
                    EOD;
                    $this->response(200, $message);
                } else {
                    /* Saving failed */
                    $this->response(500, '{"error": "Error saving request"}');
                }
            break;
        }
    }

    /**
     * Prepare data to be saved to TXT file.
     *
     * @return string
     */
    private function prepareRequestDump()
    {
        return (string) <<<EOD
        Request Key: {$this->requestKey} ({$this->requestKeyholder})
        Request Method: {$this->requestMethod}
        Content Type: {$this->requestType}
        User Agent: {$this->requestAgent}
        Received From: {$this->requestSource}
        Recevied At: {$this->requestTime}
        Request Body:
        {$this->requestPayload}
        EOD;
    }

    /**
     * Save request locally and return path and ID, or false if saving fails.
     *
     * @param string $key
     * @param string $filename
     * @param string $data
     * @return array|bool
     */
    private function dumpRequest(string $key, string $filename, string $data)
    {
        $path = DUMP_DIR.DS.$key.DS.$filename.'.txt';
        $dirname = dirname($path);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $fh = fopen($path, 'w');
        if (fwrite($fh, $data)) {
            fclose($fh);
            return ['id'=>$filename, 'path'=>$path];
        } else {
            return false;
        }
    }

    /**
     * Generate unique request ID using MD5.
     *
     * @return string
     */
    private function getUUID()
    {
        return (string) md5($this->requestSource . $this->requestTime);
    }

    /**
     * Response handler
     *
     * @param integer $code
     * @param string $message
     * @return string
     */
    private function response(int $code, string $message = '')
    {
        header('Content-Type: application/json');
        switch ($code) {
            case '401':
                http_response_code(401);
                echo '{"error": "Not Allowed"}';
                exit;
            break;

            case '200':
                http_response_code(200);
                echo $message;
            break;

            default:
                http_response_code($code);
                echo $message;
            break;
        }
    }

    /**
     * Get all requests for a key and echo HTML.
     *
     * @param string $key
     * @return void
     */
    private function getRequests(string $key)
    {
        echo "<h1>Showing requests for {$key} ($this->requestKeyholder)</h1>";
        if (is_dir("data/{$key}")) {
            echo '<table>';
            $files = scandir("data/{$key}");
            $files = array_diff($files, array('..', '.'));
            foreach ($files as $file) {
              print_r("<tr><td><a href='data/{$key}/{$file}'>{$file}</a></td>");
              print_r('<td>' . date("F d Y H:i:s", filemtime("data/{$key}/{$file}")) . '</td></tr>');
            }
            echo '</table>';
          }
    }
}

$suteba = new Suteba();
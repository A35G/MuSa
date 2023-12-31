<?php
namespace App\Core;

define("RPATH", realpath(__DIR__ . "/../"));
define('ROOT_MEDIA', RPATH . '/media/');
define('ROOT_DB', RPATH . '/dbs/');

class Musa
{
    public $settings = array();

    /**
     * Platform URI
     * 
     * @var string
     */
    private ?string $sitePath = NULL;

    /**
     * Path of multimedia files
     * 
     * @var string
     */
    private string $mediaVideo = ROOT_MEDIA . "video";

    /**
     * List of video mimetype supported and permitted
     * 
     * @var array
     */
    private array $permitVideoMime = array("video/ogg","video/mp4","video/webm");

    /**
     * Parsed informations of multimedia archive
     * 
     * @var array
     */
    private array $preloadMedia = array();

    /**
     * Default Platform language
     * 
     * @var string
     */
    private string $language;

    /**
     * Content of loaded language file
     * 
     * @var array
     */
    private array $txtLang = array();

    /**
     * Class constructor
     * 
     * @return  void
     */
    public function __construct()
    {
        $this->settings = $this->loadSettings();

        $this->loadSystemUrl();

        $dataLang = $this->loadLanguage();
        if ($dataLang !== FALSE):
            $this->txtLang = $dataLang;
        else:
            echo 'No language file!';
            exit(0);
        endif;

        $dbjs = $this->check4db();
        if ($dbjs === FALSE):
            die($this->txtLang["db_noexists_empty_noreadable"]);
        endif;
    }

    private function loadSettings()
    {
        static $settings;

        if (empty($settings)):
            $file_path = realpath(__DIR__ . "/../config/config.php");
            $found = FALSE;
            if (file_exists($file_path)):
                $found = TRUE;
                require $file_path;
            endif;

            if ( ! $found):
                echo 'The configuration file does not exist.';
                exit(3);
            endif;

            if ( ! isset($settings) OR ! is_array($settings)):
                echo 'Your config file does not appear to be formatted correctly.';
                exit(3);
            endif;
        endif;

        return $settings;
    }

    private function checkHttps(): bool
    {
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'):
            return TRUE;
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'):
            return TRUE;
        elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off'):
            return TRUE;
        endif;

        return FALSE;
    }

    private function loadSystemUrl()
    {
        if (empty($this->settings['app_url'])):
            if (isset($_SERVER['SERVER_ADDR'])):
                $serverAddr = (strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE) ? '['.$_SERVER['SERVER_ADDR'].']' : $_SERVER['SERVER_ADDR'];

                if ($serverAddr === "127.0.0.1"):
                    $serverAddr = "localhost";
                endif;

                $base_url = ($this->checkHttps() ? 'https' : 'http') . '://' . $serverAddr
                    . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
            else:
                $base_url = 'http://localhost/';
            endif;

            $this->sitePath = $base_url;
        else:
            $this->sitePath = $this->settings['app_url'];
        endif;
    }

    private function loadLanguage(string $locale = "it-IT")
    {
        $appLang = (empty($this->settings['app_lang'])) ? $locale : $this->settings['app_lang'];
        if ( ! empty($appLang)):
            if (file_exists(RPATH . "/locale/" . $appLang . ".json") !== FALSE && is_readable(RPATH . "/locale/" . $appLang . ".json") !== FALSE):
                if ($this->checkEmptyFile(RPATH . "/locale/" . $appLang . ".json") !== FALSE):
                    $l10n = file_get_contents(RPATH . "/locale/" . $appLang . ".json");
                    if ($l10n !== FALSE && ! empty($l10n) && $this->checkIsValidJSON($l10n) !== FALSE):
                        $fed = json_decode($l10n,TRUE);
                        if (isset($fed) && is_array($fed) && ! empty($fed)):
                            return $fed;
                        endif;
                    endif;
                endif;
            endif;
        endif;
        return FALSE;
    }

    private function check4db()
    {
        $response = FALSE;
        if (file_exists(ROOT_DB . "media.json") !== FALSE && is_readable(ROOT_DB . "media.json") !== FALSE):
            if ($this->checkEmptyFile(ROOT_DB . "media.json") !== FALSE):
                $this->preloadMediaDB();
                $response = TRUE;
            else:
                $eso = $this->makeArchiveVideo();
                if ($eso !== FALSE):
                    $response = self::check4db();
                endif;
            endif;
        else:
            $eso = $this->makeArchiveVideo();
            if ($eso !== FALSE):
                $response = self::check4db();
            endif;
        endif;
        return $response;
    }

    private function checkEmptyFile(string $pathFile)
    {
        clearstatcache();
        return filesize($pathFile);
    }

    private function randomString(int $length = 32)
    {
        $str = random_bytes($length);
        $str = base64_encode($str);
        $str = str_replace(["+", "/", "="], "", $str);
        $str = substr($str, 0, $length);
        return $str;
    }

    public function checkIsValidJSON(string $data = NULL)
    {
        if (NULL !== $data && ! empty($data)):
            return is_string($data) && 
              is_array(json_decode($data, TRUE)) ? TRUE : FALSE;
        endif;

        return FALSE;
    }

    private function preloadMediaDB()
    {
        if (file_exists(ROOT_DB . "media.json") !== FALSE && is_readable(ROOT_DB . "media.json") !== FALSE):
            if ($this->checkEmptyFile(ROOT_DB . "media.json") !== FALSE):
                $dm = file_get_contents(ROOT_DB . "media.json");
                if ($dm !== FALSE && ! empty($dm) && $this->checkIsValidJSON($dm) !== FALSE):
                    $this->preloadMedia = json_decode($dm,TRUE);
                endif;
            endif;
        endif;
    }

    public function getDataLanguage()
    {
        return $this->txtLang;
    }

    public function searchMediaByValue(string $mediacode = NULL)
    {
        $response = array("success" => FALSE);
        if (is_array($this->preloadMedia) && ! empty($this->preloadMedia) && array_key_exists("archive",$this->preloadMedia) !== FALSE && is_array($this->preloadMedia["archive"]) !== FALSE):
            foreach ($this->preloadMedia["archive"] as $mds):
                if (array_key_exists("msIdentity",$mds) !== FALSE):
                    if ($mediacode === $mds["msIdentity"]):
                        $dpath = $mds["msFilename"];
                        if ( ! empty($dpath)):
                            if (file_exists($this->mediaVideo . "/" . $dpath)):
                                $response = array("success" => TRUE, "title" => $mds["msTitle"], "type" => $mds["msVideoType"]);
                            else:
                                $response = array("success" => FALSE, "title" => $mds["msTitle"], "error" => "media_not_available");
                            endif;
                        else:
                            $response = array("success" => FALSE, "title" => $mds["msTitle"], "error" => "media_not_found");
                        endif;
                        break;
                    endif;
                endif;
            endforeach;
        endif;
        return json_encode($response);
    }

    public function getMediaFile(string $mediacode = NULL): string
    {
        $response = array("success" => FALSE);
        if (is_array($this->preloadMedia) && ! empty($this->preloadMedia) && array_key_exists("archive",$this->preloadMedia) !== FALSE && is_array($this->preloadMedia["archive"]) !== FALSE):
            foreach ($this->preloadMedia["archive"] as $mds):
                if (array_key_exists("msIdentity",$mds) !== FALSE && ! empty($mds["msIdentity"])):
                    if ($mediacode === $mds["msIdentity"]):
                        if (array_key_exists("msFilename",$mds) !== FALSE && ! empty($mds["msFilename"])):
                            $dpath = $mds["msFilename"];
                            if ( ! empty($dpath)):
                                if (file_exists($this->mediaVideo . "/" . $dpath)):
                                    $response = array("success" => TRUE, "mpath" => $this->mediaVideo . "/" . $dpath, "mtype" => $mds["msVideoType"]);
                                endif;
                            endif;
                            break;
                        endif;
                    endif;
                endif;
            endforeach;
        endif;
        return json_encode($response);
    }

    public function makeView(string $viewFile, array $params = NULL)
    {
        if (NULL !== $viewFile && file_exists($viewFile . ".php")):
            if (NULL !== $params && is_array($params)):
                extract($params);
                include($viewFile . ".php");
            else:
                include($viewFile . ".php");
            endif;
        endif;
    }

    public function makeUrl(string $pathFile = NULL)
    {
        return (NULL !== $pathFile) ? $this->sitePath . $pathFile : $this->sitePath;
    }

    private function makeArchiveVideo()
    {
        $response = FALSE;

        $dmc = array("archive" => array());
        $f2e = array(".DS_Store",".htaccess");

        if (file_exists(ROOT_MEDIA) !== FALSE && is_readable(ROOT_MEDIA) !== FALSE):
            if (file_exists($this->mediaVideo) !== FALSE && is_readable($this->mediaVideo)):
                $scanPath = $this->mediaVideo;

                $finfo = finfo_open(FILEINFO_MIME_TYPE);

                $flags = \FilesystemIterator::SKIP_DOTS;
                $dir = new \RecursiveDirectoryIterator($scanPath, $flags);
                $files = new \RecursiveIteratorIterator($dir);

                foreach ($files as $file):
                    if ($file->isFile()):
                        $fullName = $file->getFilename();
                        if (in_array($fullName,$f2e) === FALSE):
                            $extf = $file->getExtension();
                            $simpleName = $file->getBaseName("." . $extf);

                            $cdmd = "";
                            $dloop = TRUE;

                            while($dloop):
                                $cdmd = $this->randomString();
                                $mbyv = $this->searchMediaByValue($cdmd);
                                if (NULL !== $mbyv && ! empty($mbyv) && $this->checkIsValidJSON($mbyv) !== FALSE):
                                    $dtrsp = json_decode($mbyv,TRUE);
                                    if (array_key_exists("success",$dtrsp) !== FALSE && $dtrsp["success"] === FALSE):
                                        $dloop = FALSE;
                                    endif;
                                endif;
                            endwhile;

                            $mvFile = finfo_file($finfo, $scanPath . "/" . $fullName);

                            /*$subAvi = array("video/vnd.avi","video/msvideo","video/x-msvideo");
                            if (in_array($mvFile,$subAvi) !== FALSE):
                                $mvFile = "video/avi";
                            endif;*/

                            if (in_array($mvFile,$this->permitVideoMime) !== FALSE):
                                $dmc["archive"][] = array(
                                    "msTitle"           =>  $simpleName,
                                    "msDescription"     =>  "",
                                    "msFilename"        =>  $fullName,
                                    "msVideoType"       =>  $mvFile,
                                    "msYear"            =>  "",
                                    "msCover"           =>  "",
                                    "msIdentity"        =>  $cdmd,
                                    "msTags"            =>  array(),
                                    "msPassProtect"     =>  ""
                                );
                            endif;
                        endif;
                    endif;
                endforeach;

                finfo_close($finfo);
            endif;
        endif;

        $str = json_encode($dmc,JSON_PRETTY_PRINT);
        if (file_exists(ROOT_DB) !== FALSE && is_readable(ROOT_DB) !== FALSE && is_writable(ROOT_DB) !== FALSE):
            if (file_exists(ROOT_DB . "media.json") !== FALSE && is_writable(ROOT_DB . "media.json") !== FALSE):
                $sed = file_put_contents(ROOT_DB . "media.json",$str);
                if ($sed !== FALSE):
                    $response = TRUE;
                endif;
            else:
                $handle = fopen(ROOT_DB . "media.json", "w");
                if ($handle !== FALSE):
                    if (fwrite($handle,$str) !== FALSE):
                        $response = TRUE;
                    endif;
                endif;

                fclose($handle);
            endif;
        endif;
        return $response;
    }

    public function loadArchiveVideo()
    {
        if (is_array($this->preloadMedia) && array_key_exists("archive", $this->preloadMedia) !== FALSE && is_array($this->preloadMedia["archive"]) !== FALSE):
            $fi = 0;
            foreach ($this->preloadMedia["archive"] as $mds):
                $this->preloadMedia["archive"][$fi]["msUrlVideo"] = $this->makeUrl($mds["msIdentity"]);
                $response["archive"][] = $this->preloadMedia["archive"][$fi];
                ++$fi;
            endforeach;

            $response = array_merge($response,$this->txtLang);

            ob_start();
            $this->makeView("view/mediaBlock",$response);
            return ob_get_clean();
        endif;
        return;
    }

    public function searchMediaByForm(string $srword = NULL)
    {
        $response = array("archive" => array());

        if (is_array($this->preloadMedia) && ! empty($this->preloadMedia) && array_key_exists("archive",$this->preloadMedia) !== FALSE && is_array($this->preloadMedia["archive"]) !== FALSE):
            $g = explode(" ", $srword);
            if (is_array($g) !== FALSE):
                $fi = 0;
                foreach ($this->preloadMedia["archive"] as $mds):
                    foreach ($g as $sw):
                        if (array_key_exists("msTitle",$mds) !== FALSE):
                            if (stristr($mds["msTitle"],$sw) !== FALSE):
                                $this->preloadMedia["archive"][$fi]["msUrlVideo"] = $this->makeUrl($mds["msIdentity"]);
                                $response["archive"][] = $this->preloadMedia["archive"][$fi];
                            endif;
                        endif;
                    endforeach;

                    ++$fi;
                endforeach;
            endif;
        endif;

        return json_encode($response);
    }

}

$ms = new \stdClass();

if ($ms instanceof \stdClass):
    if (class_exists("\App\Core\Musa")):
        $ms = new Musa;
    endif;
endif;

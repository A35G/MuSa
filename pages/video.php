<?php

    if (NULL !== $_GET["s"] && strlen($_GET["s"]) > 0):

        include __DIR__ . "/../libs/Core.php";

        $cdm = filter_input(INPUT_GET, 's', FILTER_SANITIZE_ENCODED);
        if (NULL !== $cdm && $cdm !== FALSE):
            $usaf = $ms->getMediaFile($cdm);
            if (isset($usaf) && ! empty($usaf)):
                $mp = json_decode($usaf,TRUE);
                if (is_array($mp) && ! empty($mp)):
                    if (array_key_exists("success",$mp) !== FALSE && $mp["success"] !== FALSE && array_key_exists("mpath",$mp) !== FALSE && ! empty($mp["mpath"])):
                        $file_path = $mp["mpath"];

                        $fp     =   @fopen($file_path, 'rb');
                        $size   =   filesize($file_path); // File size
                        $length =   $size;           // Content length
                        $start  =   0;               // Start byte
                        $end    =   $size - 1;       // End byte

                        header('Content-type: ' . $mp["mtype"]);
                        header("Accept-Ranges: bytes");
                        if (isset($_SERVER['HTTP_RANGE'])):
                            $c_start = $start;
                            $c_end   = $end;
                            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

                            if (strpos($range, ',') !== FALSE):
                                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                header("Content-Range: bytes $start-$end/$size");
                                exit;
                            endif;

                            if ($range == '-'):
                                $c_start = $size - substr($range, 1);
                            else:
                                $range  = explode('-', $range);
                                $c_start = $range[0];
                                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                            endif;

                            $c_end = ($c_end > $end) ? $end : $c_end;
                            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size):
                                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                header("Content-Range: bytes $start-$end/$size");
                                exit;
                            endif;

                            $start  = $c_start;
                            $end    = $c_end;
                            $length = $end - $start + 1;
                            fseek($fp, $start);
                            header('HTTP/1.1 206 Partial Content');
                        endif;

                        header("Content-Range: bytes $start-$end/$size");
                        header("Content-Length: " . $length);

                        $buffer = 1024 * 8;

                        while(!feof($fp) && ($p = ftell($fp)) <= $end):
                            if ($p + $buffer > $end):
                                $buffer = $end - $p + 1;
                            endif;

                            set_time_limit(0);
                            echo fread($fp, $buffer);
                            flush();
                        endwhile;

                        fclose($fp);
                    endif;
                endif;
            endif;
        endif;
    endif;

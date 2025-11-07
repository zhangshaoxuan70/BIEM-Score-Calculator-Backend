<?php
/**
 * Simple Http Request
 * 
 * @param string $url
 * @param array $params
 * @param string $header
 * @param bool $post
 * @param string $cookie
 * @param bool $output_header
 * @param bool $allow_redirect
 * @return array Two parts of array, has error then part 0 will return 0 and other will 1, part 1 will return content
 */
function httpRequest($url, $params=[], $header="", $post = true, $cookie="", $output_header=false, $allow_redirect=true)
    {
        if($header=="")
            $header = [
                'Content-Type: application/json; charset=utf-8',
        ];

        $ch = curl_init();
        if ($post) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif (is_array($params) && 0 < count($params)) {
            curl_setopt($ch, CURLOPT_URL, $url . "?" . http_build_query($params));
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        curl_setopt($ch, CURLOPT_HEADER, $output_header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        if($cookie!="")
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $allow_redirect);

        $data = curl_exec($ch);

        //echo curl_getinfo($ch,CURLINFO_HEADER_OUT);
        if (curl_error($ch)) {
            //trigger_error(curl_error($ch));
            global $config;
            if($config['debug'])
                return [0,curl_error($ch)];
            else
                return [0,];
        }
        curl_close($ch);

        return [1,$data];
    }
?>
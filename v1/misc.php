<?php
    # Build JSON HTTP response
    function response($status, $status_message, $data)
    {
        header("HTTP/1.1 ".$status);

        $response['status'] = $status;
        $response['status_message'] = $status_message;
        $response['data'] = $data;

        $json_response = json_encode($response);
        echo $json_response;
    }

    # Return cleaned up string for SQL
    function clean_str($conn, $string)
    {
        return mysqli_real_escape_string($conn, $string);
    }

    # Return valid SQL string
    function build_str($string)
    {
        return "'".$string."'";
    }
?>
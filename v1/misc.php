<?php
    # Build JSON HTTP response
    function response($status, $status_message, $data)
    {
        header("HTTP/1.1 ".$status);

        $response['status'] = $status;
        $response['status_message'] = $status_message;
        $response['data'] = $data;

        $json_response = json_encode($response);

        if ($status <> 200)
        {
            die ($json_response);
        }
        else
        {
            echo $json_response;
        }
    }

    # Validate API token
    function validate_api_token($sql_conn, $token)
    {
        # Check for empty token
        if ($token == "")
        {
            return false;
        }

        # SQL token
        $sql_token = build_str($token);

        # Prepare and run query
        $sql_query = "SELECT * FROM ck_api_tokens WHERE token = ".$sql_token." ORDER BY token";

        # Execute query
        $result = mysqli_query($sql_conn, $sql_query);

        # Check if there were results
        if (mysqli_num_rows($result) > 0)
        {
            # TODO: Update count and lastUsage?
            return true;
        }
        else
        {
            return false;
        }

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